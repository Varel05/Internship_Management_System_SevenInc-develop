<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\InternshipRegistration as IR;

/**
 * Seed 10 akun pemagang dummy dengan berbagai status.
 * Password semua akun: password123
 *
 * Email login:
 *  pemagang1@demo.com  → waiting   (baru submit, belum diproses)
 *  pemagang2@demo.com  → waiting
 *  pemagang3@demo.com  → accepted  (sudah diterima)
 *  pemagang4@demo.com  → accepted
 *  pemagang5@demo.com  → active    (sedang magang)
 *  pemagang6@demo.com  → active
 *  pemagang7@demo.com  → completed (sudah selesai magang)
 *  pemagang8@demo.com  → completed
 *  pemagang9@demo.com  → rejected  (ditolak)
 *  pemagang10@demo.com → waiting   (draft — belum submit)
 */
class DummyPemagangSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        $data = [
            ['Budi Santoso',          'pemagang1@demo.com',  'waiting',   'Yogyakarta', 'Universitas Gadjah Mada'],
            ['Siti Rahayu',           'pemagang2@demo.com',  'waiting',   'Sleman',     'Universitas Negeri Yogyakarta'],
            ['Ahmad Fauzi',           'pemagang3@demo.com',  'active',    'Bantul',     'Universitas Atma Jaya'],
            ['Bambang Subambang',     'pemagang7@demo.com',  'completed', 'Yogyakarta', 'AMKOM Yogyakarta'],
            ['Dimas Eko Prayogo',     'pemagang9@demo.com',  'pending',   'Kulonprogo', 'SMK N 2 Yogyakarta'],
        ];

        $cityIds = \Illuminate\Support\Facades\DB::table('cities')->pluck('id')->toArray();
        $institutionIds = \Illuminate\Support\Facades\DB::table('institutions')->pluck('id')->toArray();
        $facultyIds = \Illuminate\Support\Facades\DB::table('faculties')->pluck('id')->toArray();
        $studyProgramIds = \Illuminate\Support\Facades\DB::table('study_programs')->pluck('id')->toArray();
        $divisionIds = \Illuminate\Support\Facades\DB::table('divisions')->pluck('id')->toArray();
        $brandIds = \Illuminate\Support\Facades\DB::table('brands')->pluck('id')->toArray();

        foreach ($data as $i => [$name, $email, $status, $city, $institution]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'password' => $password,
                    'role'     => 'pemagang',
                ]
            );

            if (!\Illuminate\Support\Facades\DB::table('internship_registrations')->where('user_id', $user->id)->exists()) {
                \Illuminate\Support\Facades\DB::table('internship_registrations')->insert([
                    'user_id'                => $user->id,
                    'fullname'               => $name,
                    'born_date'              => '2000-0' . ($i + 1) . '-15',
                    'student_id'             => '2021' . str_pad($i + 1, 8, '0', STR_PAD_LEFT),
                    'gender'                 => $i % 2 === 0 ? 'Laki-laki' : 'Perempuan',
                    'phone_number'           => '0812345678' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'city_id'                => !empty($cityIds) ? $cityIds[array_rand($cityIds)] : 1,
                    'institution_id'         => !empty($institutionIds) ? $institutionIds[array_rand($institutionIds)] : 1,
                    'faculty_id'             => !empty($facultyIds) ? $facultyIds[array_rand($facultyIds)] : 1,
                    'study_program_id'       => !empty($studyProgramIds) ? $studyProgramIds[array_rand($studyProgramIds)] : 1,
                    'internship_reason'      => 'Ingin mendapatkan pengalaman kerja nyata dan mengaplikasikan ilmu dari kampus.',
                    'internship_type'        => 'Magang Mandiri',
                    'internship_arrangement' => 'Onsite',
                    'current_status'         => 'Mahasiswa/Pelajar',
                    'english_book_ability'   => 'Saya bisa',
                    'division_id'            => !empty($divisionIds) ? $divisionIds[array_rand($divisionIds)] : 1,
                    'brand_id'               => !empty($brandIds) ? $brandIds[array_rand($brandIds)] : null,
                    'internship_status'      => $status,
                    'start_date'             => '2026-09-01',
                    'end_date'               => '2026-12-01',
                ]);
            }
        }
    }
}
