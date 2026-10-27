<?php

namespace App\Console\Commands;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('admin:create {--name= : Display name} {--email= : Login email} {--password= : Password (prompted for when omitted)}')]
#[Description('Create a super admin, or reset the password of an existing admin')]
class CreateAdminUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = Str::lower($this->option('email') ?: text('Email', required: true, validate: ['email' => 'email']));
        $existing = Admin::firstWhere('email', $email);

        $name = $this->option('name') ?: ($existing?->name ?? text('Name', required: true));
        $password = $this->option('password') ?: password('Password', required: true, hint: 'At least 8 characters.');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            ['email' => ['email'], 'name' => ['string', 'max:255'], 'password' => [Password::defaults()]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        if ($existing) {
            $existing->update(['name' => $name, 'password' => $password]);

            $this->components->info("Updated admin {$email}.");

            if (! $existing->is_active) {
                $this->components->warn('This account is deactivated. A super admin must reactivate it before it can sign in.');
            }
        } else {
            Admin::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => AdminRole::SuperAdmin,
                'is_active' => true,
            ]);

            $this->components->info("Created super admin {$email}.");
        }

        $this->line('  Sign in at '.route('admin.login'));

        return self::SUCCESS;
    }
}
