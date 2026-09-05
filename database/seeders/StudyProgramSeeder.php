<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prodis = [
            'Informatika','Sistem Informasi','Teknik Industri','Teknik Elektro','Teknik Mesin',
            'Manajemen','Akuntansi','Ilmu Komunikasi','Desain Komunikasi Visual','Desain Produk',
            'Agribisnis','Teknik Sipil','Teknik Arsitektur','Teknologi Informasi','Ilmu Hukum'
        ];
        $facultyIds = \Illuminate\Support\Facades\DB::table('faculties')->pluck('id')->toArray();
        
        foreach ($facultyIds as $facId) {
            // Pick a few random study programs for each faculty to simulate realism
            $randomProdis = collect($prodis)->random(5);
            foreach ($randomProdis as $prodi) {
                \Illuminate\Support\Facades\DB::table('study_programs')->insert([
                    'faculty_id' => $facId,
                    'name' => $prodi
                ]);
            }
        }
    }
}
