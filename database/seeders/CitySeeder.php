<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = ['Jakarta','Bandung','Bekasi','Depok','Tangerang','Bogor','Cimahi','Cirebon','Tasikmalaya','Semarang','Solo','Yogyakarta','Magelang','Surabaya','Sidoarjo','Gresik','Malang','Kediri','Jember','Banyuwangi','Denpasar','Mataram','Medan','Padang','Pekanbaru','Palembang','Lampung','Pontianak','Banjarmasin','Makassar', 'Sleman', 'Bantul', 'Kulonprogo'];
        foreach ($cities as $city) {
            \Illuminate\Support\Facades\DB::table('cities')->insert(['name' => $city]);
        }
    }
}
