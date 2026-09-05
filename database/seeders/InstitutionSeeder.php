<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = [
            'Universitas Gadjah Mada','Universitas Indonesia','Institut Teknologi Bandung','Universitas Brawijaya',
            'Universitas Negeri Yogyakarta','Universitas Diponegoro','Universitas Sebelas Maret','Universitas Airlangga',
            'Universitas Islam Indonesia','Universitas Muhammadiyah Yogyakarta','Universitas Telkom','Universitas Negeri Malang',
            'Politeknik Negeri Malang','Politeknik Negeri Bandung','Politeknik Elektronika Negeri Surabaya',
            'SMK Negeri 1 Yogyakarta','SMK Negeri 2 Surabaya','SMK Telkom Malang', 'Universitas Atma Jaya', 'AMKOM Yogyakarta', 'Universitas Mercu Buana', 'SMK N 2 Yogyakarta', 'Universitas Sanata Dharma'
        ];
        foreach ($institutions as $inst) {
            \Illuminate\Support\Facades\DB::table('institutions')->insert(['name' => $inst]);
        }
    }
}
