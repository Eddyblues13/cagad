<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Create the default super admin described in config/admin.php.
     *
     * Safe to re-run: an existing admin with the same email is left exactly as
     * it is, so seeding never resets a password that has since been changed.
     */
    public function run(): void
    {
        ['name' => $name, 'email' => $email, 'password' => $password] = config('admin.seed');
        $output = $this->command?->outputComponents();

        if (Admin::where('email', Str::lower($email))->exists()) {
            $output?->info("Admin {$email} already exists, so it was left unchanged.");

            return;
        }

        $passwordWasGenerated = blank($password);

        if ($passwordWasGenerated) {
            $password = app()->isLocal() ? 'password' : Str::password(20);
        }

        Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => AdminRole::SuperAdmin,
            'is_active' => true,
        ]);

        $output?->info('Created the default super admin.');
        $output?->twoColumnDetail('Email', $email);
        $output?->twoColumnDetail('Password', $passwordWasGenerated ? $password : 'ADMIN_PASSWORD from .env');
        $output?->twoColumnDetail('Sign in at', route('admin.login'));

        if ($passwordWasGenerated) {
            $output?->warn('Change this password on the Account page after signing in.');
        }
    }
}
