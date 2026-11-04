<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Safe to re-run: existing admins and categories are left alone, and sample
     * posts are only added while the blog has no posts at all.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
            PostSeeder::class,
        ]);
    }
}
