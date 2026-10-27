<?php

namespace App\Providers;

use App\Services\Cloudinary;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Cloudinary::class, fn (): Cloudinary => new Cloudinary(
            cloudName: config('services.cloudinary.cloud_name'),
            apiKey: config('services.cloudinary.api_key'),
            apiSecret: config('services.cloudinary.api_secret'),
            folder: config('services.cloudinary.folder'),
        ));

        // Post bodies come from the rich-text editor and are rendered as raw HTML
        // by the React site, so everything is cleaned before it reaches the database.
        $this->app->singleton(HtmlSanitizerInterface::class, fn (): HtmlSanitizer => new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->allowMediaSchemes(['https'])
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
                ->withMaxInputLength(1_000_000)
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(120)->by($request->ip()));

        Paginator::useBootstrapFive();
    }
}
