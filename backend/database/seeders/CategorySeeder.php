<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed CAGAD's focus areas as the starting blog categories.
     */
    public function run(): void
    {
        $categories = [
            'Governance' => 'Institutions, public administration and the quality of government.',
            'Accountability' => 'Transparency, oversight and holding power to account.',
            'Security' => 'Peace, security and the rule of law across Nigeria and West Africa.',
            'Democracy' => 'Elections, participation and democratic institutions.',
            'Peacebuilding' => 'Conflict prevention, resolution and social cohesion.',
            'Sustainable Development' => 'Inclusive growth, public services and development policy.',
            'News & Events' => 'Updates, announcements and event reports from CAGAD.',
        ];

        foreach ($categories as $name => $description) {
            Category::firstOrCreate(['name' => $name], ['description' => $description]);
        }
    }
}
