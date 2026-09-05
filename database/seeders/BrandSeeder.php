<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::table('brands')->insert([
            'code' => 'SVII',
            'name' => 'SevenInc',
            'company_address' => 'Jl. Janti Gg. Arjuna No.59, Karangjambe, Banguntapan, Bantul',
            'signatory_name' => 'John Doe',
            'signatory_position' => 'CEO',
        ]);
        \Illuminate\Support\Facades\DB::table('brands')->insert([
            'code' => 'ALB',
            'name' => 'Alona',
            'company_address' => 'Yogyakarta',
            'signatory_name' => 'Jane Smith',
            'signatory_position' => 'Manager',
        ]);
    }
}
