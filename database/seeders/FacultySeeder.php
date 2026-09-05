<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faculties = ['Teknik','Ilmu Komputer','Ekonomi & Bisnis','Ilmu Sosial & Politik','Seni & Desain','Kedokteran','Pertanian','Vokasi'];
        $institutionIds = \Illuminate\Support\Facades\DB::table('institutions')->pluck('id')->toArray();
        
        foreach ($institutionIds as $instId) {
            foreach ($faculties as $fac) {
                \Illuminate\Support\Facades\DB::table('faculties')->insert([
                    'institution_id' => $instId,
                    'name' => $fac
                ]);
            }
        }
    }
}
