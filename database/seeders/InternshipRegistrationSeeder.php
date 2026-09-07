<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\InternshipRegistration as IR;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class InternshipRegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $firstM = ['Muhammad','Ahmad','Nur','Rizky','Agus','Budi','Andi','Fajar','Arief','Rian','Bayu','Rizal','Fadil','Dwi','Dimas','Eko','Yoga','Ilham','Fauzan','Rafli','Yusuf','Heri','Dede','Galang','Aditya','Bagus','Zaki','Iqbal','Rangga','Reza'];
        $firstF = ['Siti','Nurul','Putri','Ayu','Dewi','Lia','Nadia','Intan','Rani','Dinda','Citra','Wulan','Anisa','Maya','Bella','Sarah','Rizka','Nabila','Fitri','Aulia','Tyas','Niken','Mega','Hana','Zahra','Indah','Silvia','Risa','Yuni','Vina'];
        $last    = ['Pratama','Saputra','Permana','Ramadhan','Santoso','Hidayat','Wijaya','Wibowo','Prasetyo','Fauzi','Kurniawan','Setiawan','Maulana','Pangestu','Firmansyah','Alamsyah','Siregar','Simanjuntak','Sihombing','Nugroho','Susanto','Santika','Lestari','Safitri','Utami','Herlambang','Cahyono','Kusuma','Wardani','Puspitasari'];

        $internTypes = ['Magang Mandiri','Magang Kampus','PKL','Kampus Merdeka'];
        $arrangements = ['Onsite','Hibrida','Remote'];
        $currentStatus = ['Mahasiswa/Pelajar','Lulusan Baru','Karyawan','Tidak Bekerja'];
        $engAbility = ['Saya bisa','Kurang bisa','Tidak bisa'];
        $toolsList  = ['Corel / Photoshop','Adobe Premiere / After Effects','Kamera','Drone','Pen Tablet','Tripod'];
        $sources    = ['website','instagram','twitter','glints','youtube'];

        $statusWeighted = ['active', 'active', 'active', 'active', 'completed', 'completed', 'completed', 'waiting', 'waiting', 'pending', 'exited'];

        $pick = fn(array $a) => $a[array_rand($a)];
        $randBool = fn(int $pctTrue = 50) => mt_rand(1,100) <= $pctTrue;
        $randDate = function(string $min, string $max) {
            return date('Y-m-d', mt_rand(strtotime($min), strtotime($max)));
        };

        $cityIds = DB::table('cities')->pluck('id')->toArray();
        $institutionIds = DB::table('institutions')->pluck('id')->toArray();
        $facultyIds = DB::table('faculties')->pluck('id')->toArray();
        $studyProgramIds = DB::table('study_programs')->pluck('id')->toArray();
        $divisionIds = DB::table('divisions')->pluck('id')->toArray();
        $brandIds = DB::table('brands')->pluck('id')->toArray();
        
        $password = Hash::make('password123');

        for ($i=1; $i<=50; $i++) { // Reduced to 50 for faster seeding
            $isMale = $randBool(55);
            $fname  = $isMale ? $pick($firstM) : $pick($firstF);
            $lname  = $pick($last);
            $mname  = $randBool(35) ? ' '.$pick($last) : '';
            $fullname = trim("$fname$mname $lname");

            $born = $randDate('1999-01-01','2007-12-31');
            $studentId = sprintf('%02d%02d%04d', mt_rand(18,25), mt_rand(1,14), mt_rand(1000,9999));
            $userSlug = Str::slug($fullname, '.');
            $email = $userSlug.mt_rand(1,999).'@gmail.com';
            $phone = '08'.mt_rand(11,99).mt_rand(100,999).mt_rand(1000,9999);

            $reason = $pick([
                'Ingin menambah pengalaman kerja nyata',
                'Mencari bimbingan praktis sesuai jurusan',
                'Butuh tempat magang untuk syarat kampus'
            ]);

            // 1. Create User
            $userId = DB::table('users')->insertGetId([
                'email' => $email,
                'password' => $password,
                'role' => 'pemagang',
            ]);

            // 2. Create Registration
            $regId = DB::table('internship_registrations')->insertGetId([
                'user_id' => $userId,
                'fullname' => $fullname,
                'born_date' => $born,
                'gender' => $isMale ? 'Laki-laki' : 'Perempuan',
                'phone_number' => $phone,
                'city_id' => $pick($cityIds),
                'student_id' => $studentId,
                'institution_id' => $pick($institutionIds),
                'faculty_id' => $pick($facultyIds),
                'study_program_id' => $pick($studyProgramIds),
                'internship_reason' => $reason,
                'internship_type' => $pick($internTypes),
                'internship_arrangement' => $pick($arrangements),
                'current_status' => $pick($currentStatus),
                'english_book_ability' => $pick($engAbility),
                'division_id' => $pick($divisionIds),
                'brand_id' => $pick($brandIds),
                'internship_status' => $pick($statusWeighted),
                'start_date' => $randDate('2026-08-01', '2026-10-01'),
                'end_date' => $randDate('2026-12-01', '2027-02-01'),
            ]);

            // 3. Create Pivots
            // Tools
            if ($randBool(70)) {
                $tools = collect($toolsList)->random(mt_rand(1, 3));
                foreach ($tools as $tool) {
                    DB::table('intern_tools')->insert([
                        'internship_registration_id' => $regId,
                        'tool_name' => $tool
                    ]);
                }
            }
            
            // Skills
            if ($randBool(80)) {
                $categories = ['design', 'video', 'programming', 'digital_marketing'];
                DB::table('intern_skills')->insert([
                    'internship_registration_id' => $regId,
                    'skill_category' => $pick($categories),
                    'skill_name' => 'Basic Skill',
                ]);
            }

            // Info Sources
            if ($randBool(90)) {
                $srcs = collect($sources)->random(mt_rand(1, 2));
                foreach ($srcs as $src) {
                    DB::table('intern_info_sources')->insert([
                        'internship_registration_id' => $regId,
                        'source_name' => $src
                    ]);
                }
            }
        }
    }
}
