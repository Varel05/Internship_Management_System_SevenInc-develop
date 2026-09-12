<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['name' => 'Administration', 'code' => 'ADM'],
            ['name' => 'Human Resources (HR)', 'code' => 'HRD'],
            ['name' => 'UI/UX Designer', 'code' => 'UIUX'],
            ['name' => 'Programmer (Front End / Backend)', 'code' => 'PROG'],
            ['name' => 'Photographer', 'code' => 'FOTO'],
            ['name' => 'Videographer', 'code' => 'VIDEO'],
            ['name' => 'Graphic Designer (Konten Kreatif)', 'code' => 'GD'],
            ['name' => 'Social Media Specialist', 'code' => 'SOCMED'],
            ['name' => 'Content Writer', 'code' => 'CW'],
            ['name' => 'Content Planner', 'code' => 'CP'],
            ['name' => 'Sales & Marketing', 'code' => 'SALES'],
            ['name' => 'Public Relations (Marcomm)', 'code' => 'PR'],
            ['name' => 'Digital Marketing', 'code' => 'DM'],
            ['name' => 'TikTok Creator', 'code' => 'TIKTOK'],
            ['name' => 'Project Manager', 'code' => 'PM'],
            ['name' => 'Pengelasan', 'code' => 'LAS'],
            ['name' => 'Animasi', 'code' => 'ANIM'],
            ['name' => 'Customer Service', 'code' => 'CS'],
        ];

        foreach ($divisions as $division) {
            \Illuminate\Support\Facades\DB::table('divisions')->insert([
                'name' => $division['name'],
                'slug' => \Illuminate\Support\Str::slug($division['name']),
                'code' => $division['code'],
                'is_active' => true,
            ]);
        }
    }
}
