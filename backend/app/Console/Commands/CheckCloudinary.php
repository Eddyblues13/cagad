<?php

namespace App\Console\Commands;

use App\Exceptions\CloudinaryException;
use App\Services\Cloudinary;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cloudinary:check')]
#[Description('Verify the Cloudinary credentials in .env')]
class CheckCloudinary extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(Cloudinary $cloudinary): int
    {
        try {
            $cloudinary->ping();
        } catch (CloudinaryException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info(sprintf(
            'Cloudinary is reachable. Cloud "%s", uploading into "%s/".',
            config('services.cloudinary.cloud_name'),
            config('services.cloudinary.folder'),
        ));

        return self::SUCCESS;
    }
}
