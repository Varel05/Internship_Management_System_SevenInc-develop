<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\InternshipRegistration;
use App\Models\Division;
use Illuminate\Support\Facades\Hash;

class PemagangRegistrationRequiredFieldsTest extends TestCase
{
    public function test_can_submit_registration_with_only_data_diri_mahasiswa_and_magang(): void
    {
        $division = Division::firstOrCreate(['name' => 'Programmer (Front End / Backend)']);

        $user = User::create([
            'name' => 'pemagang_submit_' . time(),
            'email' => 'pemagang_submit_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $payload = [
            // Data Diri (Wajib)
            'fullname'     => 'Budi Santoso',
            'gender'       => 'Laki-laki',
            'born_date'    => '2001-05-20',
            'current_city' => 'Yogyakarta',
            'email'        => $user->email,
            'phone_number' => '081234567890',

            // Data Mahasiswa (Wajib)
            'current_status'   => 'Mahasiswa/Pelajar',
            'student_id'       => '12345678',
            'institution_name' => 'Universitas Gadjah Mada',
            'faculty'          => 'Teknik',
            'study_program'    => 'Teknologi Informasi',

            // Data Magang (Wajib)
            'internship_interest'    => $division->name,
            'internship_type'        => 'Magang Mandiri',
            'internship_reason'      => 'Ingin menambah pengalaman kerja profesional.',

            // Semua field pendukung (berkas, skill, english, pembimbing, kost, ig) KOSONG / TIDAK DIISI
        ];

        $response = $this->actingAs($user)
            ->post(route('pemagang.registration.store'), $payload);

        $response->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('internship_registrations', [
            'user_id'  => $user->id,
            'fullname' => 'Budi Santoso',
            'student_id' => '12345678',
        ]);

        // Clean up
        InternshipRegistration::where('user_id', $user->id)->delete();
        $user->delete();
    }

    public function test_fails_when_data_diri_or_mahasiswa_or_magang_is_missing(): void
    {
        $user = User::create([
            'name' => 'pemagang_fail_' . time(),
            'email' => 'pemagang_fail_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // Kirim tanpa fullname, student_id, internship_interest
        $response = $this->actingAs($user)
            ->from(route('pemagang.registration.form'))
            ->post(route('pemagang.registration.store'), [
                'email' => $user->email,
            ]);

        $response->assertSessionHasErrors([
            'fullname',
            'born_date',
            'gender',
            'current_city',
            'phone_number',
            'current_status',
            'student_id',
            'institution_name',
            'faculty',
            'study_program',
            'internship_interest',
            'internship_type',
            'internship_reason',
        ]);

        // Clean up
        $user->delete();
    }
}
