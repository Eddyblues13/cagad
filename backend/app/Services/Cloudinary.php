<?php

namespace App\Services;

use App\Exceptions\CloudinaryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Minimal client for Cloudinary's signed Upload API.
 *
 * Cloudinary's PHP SDK still requires Guzzle 7, which conflicts with the Guzzle 8
 * that ships with Laravel 13, so this talks to the REST API through Laravel's
 * HTTP client instead. It only covers what the blog needs: upload and destroy.
 */
class Cloudinary
{
    private const API_BASE = 'https://api.cloudinary.com/v1_1';

    public function __construct(
        private readonly ?string $cloudName,
        private readonly ?string $apiKey,
        private readonly ?string $apiSecret,
        private readonly string $folder,
    ) {}

    public function isConfigured(): bool
    {
        return filled($this->cloudName) && filled($this->apiKey) && filled($this->apiSecret);
    }

    /**
     * Upload an image into "{folder}/{subfolder}" and return where it lives.
     *
     * @return array{public_id: string, url: string, width: int, height: int}
     *
     * @throws CloudinaryException
     */
    public function upload(UploadedFile $file, string $subfolder): array
    {
        $this->ensureConfigured();

        $params = $this->sign([
            'folder' => trim($this->folder.'/'.$subfolder, '/'),
            'timestamp' => (string) now()->timestamp,
        ]);

        try {
            $response = $this->http()
                ->timeout(90)
                ->attach('file', $file->get(), $file->getClientOriginalName())
                ->post($this->endpoint('image/upload'), $params);
        } catch (ConnectionException $exception) {
            throw new CloudinaryException('Could not reach Cloudinary. Check the connection and try again.', previous: $exception);
        }

        if ($response->failed() || blank($response->json('secure_url'))) {
            throw new CloudinaryException($response->json('error.message') ?? 'Cloudinary rejected the upload.');
        }

        return [
            'public_id' => $response->json('public_id'),
            'url' => $response->json('secure_url'),
            'width' => (int) $response->json('width'),
            'height' => (int) $response->json('height'),
        ];
    }

    /**
     * Delete an image and purge it from the CDN. Failures are logged rather than
     * thrown so a hiccup at Cloudinary never blocks an editor from saving.
     */
    public function destroy(string $publicId): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $response = $this->http()->asForm()->post($this->endpoint('image/destroy'), $this->sign([
                'invalidate' => 'true',
                'public_id' => $publicId,
                'timestamp' => (string) now()->timestamp,
            ]));
        } catch (ConnectionException $exception) {
            Log::warning('Cloudinary delete failed: could not connect.', ['public_id' => $publicId, 'error' => $exception->getMessage()]);

            return false;
        }

        if (in_array($response->json('result'), ['ok', 'not found'], true)) {
            return true;
        }

        Log::warning('Cloudinary delete failed.', ['public_id' => $publicId, 'response' => $response->json()]);

        return false;
    }

    /**
     * Check the credentials against Cloudinary's Admin API.
     *
     * @throws CloudinaryException
     */
    public function ping(): void
    {
        $this->ensureConfigured();

        try {
            $response = $this->http()
                ->withBasicAuth($this->apiKey, $this->apiSecret)
                ->get($this->endpoint('ping'));
        } catch (ConnectionException $exception) {
            throw new CloudinaryException('Could not reach Cloudinary.', previous: $exception);
        }

        if ($response->json('status') !== 'ok') {
            throw new CloudinaryException($response->json('error.message') ?? "Cloudinary answered with HTTP {$response->status()}.");
        }
    }

    /**
     * Add a delivery transformation (e.g. "c_fill,w_800,h_450") to a Cloudinary
     * image URL, always serving the best format and quality for the browser.
     * URLs that are not Cloudinary uploads are returned untouched.
     */
    public static function transform(string $url, string $transformation): string
    {
        if (! Str::contains($url, '/image/upload/')) {
            return $url;
        }

        return Str::replaceFirst('/image/upload/', "/image/upload/{$transformation},f_auto,q_auto/", $url);
    }

    private function http(): PendingRequest
    {
        return Http::acceptJson()->timeout(30);
    }

    private function endpoint(string $path): string
    {
        return self::API_BASE."/{$this->cloudName}/{$path}";
    }

    /**
     * Sign request parameters as described in Cloudinary's authentication docs:
     * sort by key, join as a query string, append the secret and SHA-1 it.
     *
     * @param  array<string, string>  $params
     * @return array<string, string>
     */
    private function sign(array $params): array
    {
        ksort($params);

        $payload = collect($params)
            ->map(fn (string $value, string $key): string => "{$key}={$value}")
            ->implode('&');

        return [
            ...$params,
            'api_key' => $this->apiKey,
            'signature' => sha1($payload.$this->apiSecret),
        ];
    }

    /**
     * @throws CloudinaryException
     */
    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new CloudinaryException('Cloudinary is not configured. Set CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY and CLOUDINARY_API_SECRET in .env.');
        }
    }
}
