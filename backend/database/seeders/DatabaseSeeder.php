<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Sample posts are only added in the local environment, so they can never
     * end up on the live blog.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            CategorySeeder::class,
        ]);

        if (app()->isLocal()) {
            $this->call(PostSeeder::class);
        }
    }
}
