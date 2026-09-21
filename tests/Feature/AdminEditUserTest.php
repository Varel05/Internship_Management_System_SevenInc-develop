<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\City;
use App\Models\Institution;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\Division;
use App\Models\InternshipRegistration;
use Illuminate\Support\Facades\Hash;

class AdminEditUserTest extends TestCase
{
    public function test_admin_can_view_edit_user_page(): void
    {
        $admin = User::create([
            'name'     => 'admin_edit_v_' . time(),
            'email'    => 'admin_edit_v_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        $targetUser = User::create([
            'name'     => 'target_v_' . time(),
            'email'    => 'target_v_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'pemagang',
        ]);

        // Berikan registrasi
        $city = City::first() ?? City::create(['name' => 'Yogyakarta']);
        $inst = Institution::first() ?? Institution::create(['name' => 'Universitas Contoh', 'type' => 'Universitas']);
        $fac = Faculty::first() ?? Faculty::create(['name' => 'Fakultas Teknik', 'institution_id' => $inst->id]);
        $sp = StudyProgram::first() ?? StudyProgram::create(['name' => 'Informatika', 'faculty_id' => $fac->id]);
        $div = Division::first() ?? Division::create(['name' => 'Web Development']);

        $registration = InternshipRegistration::create([
            'user_id'                => $targetUser->id,
            'fullname'               => 'Nama Asli Pemagang',
            'born_date'              => '2000-01-01',
            'gender'                 => 'Laki-laki',
            'phone_number'           => '08111111111',
            'city_id'                => $city->id,
            'student_id'             => '12345678',
            'institution_id'         => $inst->id,
            'faculty_id'             => $fac->id,
            'study_program_id'       => $sp->id,
            'internship_reason'      => 'Ingin belajar',
            'internship_type'        => 'Magang Mandiri',
            'internship_arrangement' => 'Onsite',
            'current_status'         => 'Mahasiswa/Pelajar',
            'english_book_ability'   => 'Saya bisa',
            'division_id'            => $div->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $targetUser->id));
        $response->assertStatus(200);
        $response->assertSee('name="name"', false);
        $response->assertSee('name="fullname"', false);
        $response->assertSee('name="phone_number"', false);

        $registration->delete();
        $targetUser->delete();
        $admin->delete();
    }

    public function test_admin_can_update_user_name_role_and_phone(): void
    {
        $admin = User::create([
            'name'     => 'admin_upd_' . time(),
            'email'    => 'admin_upd_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        $targetUser = User::create([
            'name'     => 'target_u_' . time(),
            'email'    => 'target_u_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'pemagang',
        ]);

        // Berikan registrasi untuk target user
        $city = City::first() ?? City::create(['name' => 'Yogyakarta']);
        $inst = Institution::first() ?? Institution::create(['name' => 'Universitas Contoh', 'type' => 'Universitas']);
        $fac = Faculty::first() ?? Faculty::create(['name' => 'Fakultas Teknik', 'institution_id' => $inst->id]);
        $sp = StudyProgram::first() ?? StudyProgram::create(['name' => 'Informatika', 'faculty_id' => $fac->id]);
        $div = Division::first() ?? Division::create(['name' => 'Web Development']);

        $registration = InternshipRegistration::create([
            'user_id'                => $targetUser->id,
            'fullname'               => 'Nama Asli Pemagang',
            'born_date'              => '2000-01-01',
            'gender'                 => 'Laki-laki',
            'phone_number'           => '08111111111',
            'city_id'                => $city->id,
            'student_id'             => '12345678',
            'institution_id'         => $inst->id,
            'faculty_id'             => $fac->id,
            'study_program_id'       => $sp->id,
            'internship_reason'      => 'Ingin belajar',
            'internship_type'        => 'Magang Mandiri',
            'internship_arrangement' => 'Onsite',
            'current_status'         => 'Mahasiswa/Pelajar',
            'english_book_ability'   => 'Saya bisa',
            'division_id'            => $div->id,
        ]);

        $newUserName = 'new_username_' . time();
        $newFullName = 'Nama Lengkap Baru';
        $newPhone = '08999999999';

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser->id), [
            'name'         => $newUserName,
            'fullname'     => $newFullName,
            'phone_number' => $newPhone,
            'role'         => 'admin',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Verifikasi tabel users
        $this->assertDatabaseHas('users', [
            'id'   => $targetUser->id,
            'name' => $newUserName,
            'role' => 'admin',
        ]);

        // Verifikasi tabel internship_registrations
        $this->assertDatabaseHas('internship_registrations', [
            'user_id'      => $targetUser->id,
            'fullname'     => $newFullName,
            'phone_number' => $newPhone,
        ]);

        // Bersihkan
        $registration->delete();
        $targetUser->delete();
        $admin->delete();
    }

    public function test_admin_cannot_update_user_with_duplicate_username(): void
    {
        $admin = User::create([
            'name'     => 'admin_dup_test_' . time(),
            'email'    => 'admin_dup_test_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        $otherUser = User::create([
            'name'     => 'existing_name_' . time(),
            'email'    => 'existing_name_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'pemagang',
        ]);

        $targetUser = User::create([
            'name'     => 'target_dup_' . time(),
            'email'    => 'target_dup_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'pemagang',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.users.edit', $targetUser->id))
            ->put(route('admin.users.update', $targetUser->id), [
                'name' => $otherUser->name,
                'role' => 'pemagang',
            ]);

        $response->assertSessionHasErrors('name');

        $targetUser->delete();
        $otherUser->delete();
        $admin->delete();
    }
}
