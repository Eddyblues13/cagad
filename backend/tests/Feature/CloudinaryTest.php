<?php

namespace Tests\Feature;

use App\Exceptions\CloudinaryException;
use App\Services\Cloudinary;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CloudinaryTest extends TestCase
{
    public function test_uploads_are_signed_and_filed_under_the_configured_folder(): void
    {
        $this->freezeSecond();
        Http::fake([
            'api.cloudinary.com/v1_1/test-cloud/image/upload' => Http::response([
                'public_id' => 'cagad/blog/covers/abc',
                'secure_url' => 'https://res.cloudinary.com/test-cloud/image/upload/v1/cagad/blog/covers/abc.jpg',
                'width' => 1200,
                'height' => 800,
            ]),
        ]);

        $image = app(Cloudinary::class)->upload(UploadedFile::fake()->image('photo.jpg'), 'covers');

        $this->assertSame([
            'public_id' => 'cagad/blog/covers/abc',
            'url' => 'https://res.cloudinary.com/test-cloud/image/upload/v1/cagad/blog/covers/abc.jpg',
            'width' => 1200,
            'height' => 800,
        ], $image);

        $timestamp = now()->timestamp;
        $expectedSignature = sha1("folder=cagad/blog/covers&timestamp={$timestamp}test-secret");

        Http::assertSent(function (Request $request) use ($expectedSignature): bool {
            $fields = collect($request->data())->pluck('contents', 'name');

            return $request->isMultipart()
                && $fields['folder'] === 'cagad/blog/covers'
                && $fields['api_key'] === 'test-key'
                && $fields['signature'] === $expectedSignature
                && $request->hasFile('file');
        });
    }

    public function test_a_rejected_upload_throws_with_cloudinarys_message(): void
    {
        Http::fake(['api.cloudinary.com/*' => Http::response(['error' => ['message' => 'Invalid image file']], 400)]);

        $this->expectException(CloudinaryException::class);
        $this->expectExceptionMessage('Invalid image file');

        app(Cloudinary::class)->upload(UploadedFile::fake()->image('photo.jpg'), 'covers');
    }

    public function test_destroy_invalidates_the_cdn_copy(): void
    {
        Http::fake(['api.cloudinary.com/v1_1/test-cloud/image/destroy' => Http::response(['result' => 'ok'])]);

        $this->assertTrue(app(Cloudinary::class)->destroy('cagad/blog/covers/abc'));

        Http::assertSent(fn (Request $request): bool => $request['public_id'] === 'cagad/blog/covers/abc'
            && $request['invalidate'] === 'true'
            && filled($request['signature']));
    }

    public function test_a_failed_destroy_is_reported_without_throwing(): void
    {
        Http::fake(['api.cloudinary.com/*' => Http::response(['error' => ['message' => 'Server error']], 500)]);

        $this->assertFalse(app(Cloudinary::class)->destroy('cagad/blog/covers/abc'));
    }

    public function test_uploading_without_credentials_explains_what_to_set(): void
    {
        $cloudinary = new Cloudinary(cloudName: null, apiKey: null, apiSecret: null, folder: 'cagad/blog');

        $this->expectException(CloudinaryException::class);
        $this->expectExceptionMessage('CLOUDINARY_CLOUD_NAME');

        $cloudinary->upload(UploadedFile::fake()->image('photo.jpg'), 'covers');
    }

    public function test_transform_adds_delivery_options_and_leaves_other_urls_alone(): void
    {
        $this->assertSame(
            'https://res.cloudinary.com/x/image/upload/c_fill,w_400,f_auto,q_auto/v1/a/b.jpg',
            Cloudinary::transform('https://res.cloudinary.com/x/image/upload/v1/a/b.jpg', 'c_fill,w_400'),
        );

        $this->assertSame(
            'https://example.org/photo.jpg',
            Cloudinary::transform('https://example.org/photo.jpg', 'c_fill,w_400'),
        );
    }

    public function test_the_check_command_reports_valid_credentials(): void
    {
        Http::fake(['api.cloudinary.com/v1_1/test-cloud/ping' => Http::response(['status' => 'ok'])]);

        $this->artisan('cloudinary:check')
            ->expectsOutputToContain('Cloudinary is reachable')
            ->assertSuccessful();
    }

    public function test_the_check_command_fails_on_bad_credentials(): void
    {
        Http::fake(['api.cloudinary.com/*' => Http::response(['error' => ['message' => 'Invalid api_key test-key']], 401)]);

        $this->artisan('cloudinary:check')
            ->expectsOutputToContain('Invalid api_key')
            ->assertFailed();
    }
}
