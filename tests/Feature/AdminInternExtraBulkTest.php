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
use App\Models\InternExtra;
use Illuminate\Support\Facades\Hash;

class AdminInternExtraBulkTest extends TestCase
{
    private function createCompletedIntern($name)
    {
        $user = User::create([
            'name'     => 'u_' . uniqid(),
            'email'    => uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'pemagang',
        ]);

        $city = City::first() ?? City::create(['name' => 'Yogyakarta']);
        $inst = Institution::first() ?? Institution::create(['name' => 'Universitas Contoh', 'type' => 'Universitas']);
        $fac  = Faculty::first() ?? Faculty::create(['name' => 'Fakultas Teknik', 'institution_id' => $inst->id]);
        $sp   = StudyProgram::first() ?? StudyProgram::create(['name' => 'Informatika', 'faculty_id' => $fac->id]);
        $div  = Division::first() ?? Division::create(['name' => 'Web Development']);

        $intern = InternshipRegistration::create([
            'user_id'                => $user->id,
            'fullname'               => $name,
            'born_date'              => '2000-01-01',
            'gender'                 => 'Laki-laki',
            'phone_number'           => '08123456789',
            'city_id'                => $city->id,
            'student_id'             => '12345678',
            'institution_id'         => $inst->id,
            'faculty_id'             => $fac->id,
            'study_program_id'       => $sp->id,
            'internship_reason'      => 'Belajar',
            'internship_type'        => 'Magang Mandiri',
            'internship_arrangement' => 'Onsite',
            'current_status'         => 'Mahasiswa/Pelajar',
            'english_book_ability'   => 'Saya bisa',
            'division_id'            => $div->id,
            'internship_status'      => InternshipRegistration::STATUS_COMPLETED,
        ]);

        return [$user, $intern];
    }

    public function test_admin_can_view_checkboxes_on_informasi_alumni(): void
    {
        $admin = User::create([
            'name'     => 'admin_alumni_' . time(),
            'email'    => 'admin_alumni_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        [$u1, $intern1] = $this->createCompletedIntern('Alumni Satu');
        [$u2, $intern2] = $this->createCompletedIntern('Alumni Dua');

        $response = $this->actingAs($admin)->get(route('admin.intern_extras.index'));
        $response->assertStatus(200);

        // Checkbox header dan row
        $response->assertSee('id="checkAll"', false);
        $response->assertSee('class="intern-checkbox', false);
        $response->assertSee('id="bulkActionBar"', false);
        $response->assertSee('Bulk Kelola Alumni');

        // Cleanup
        $intern1->delete();
        $intern2->delete();
        $u1->delete();
        $u2->delete();
        $admin->delete();
    }

    public function test_admin_can_view_bulk_edit_page(): void
    {
        $admin = User::create([
            'name'     => 'admin_blk_' . time(),
            'email'    => 'admin_blk_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        [$u1, $intern1] = $this->createCompletedIntern('Alumni Alpha');
        [$u2, $intern2] = $this->createCompletedIntern('Alumni Beta');

        $response = $this->actingAs($admin)->get(
            route('admin.intern_extras.edit', $intern1->id) . "?mode=bulk&ids={$intern1->id},{$intern2->id}"
        );

        $response->assertStatus(200);
        $response->assertSee('Mode: Bulk Kelola');
        $response->assertSee('Alumni Alpha');
        $response->assertSee('Alumni Beta');

        // Cleanup
        $intern1->delete();
        $intern2->delete();
        $u1->delete();
        $u2->delete();
        $admin->delete();
    }

    public function test_admin_can_update_extras_in_bulk(): void
    {
        $admin = User::create([
            'name'     => 'admin_upd_blk_' . time(),
            'email'    => 'admin_upd_blk_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);

        [$u1, $intern1] = $this->createCompletedIntern('Alumni Pertama');
        [$u2, $intern2] = $this->createCompletedIntern('Alumni Kedua');

        $groupUrl = 'https://chat.whatsapp.com/samplegroup123';
        $groupLabel = 'Grup Alumni Terpilih';
        $jobUrl = 'https://jobs.example.com/listing';

        $response = $this->actingAs($admin)->put(route('admin.intern_extras.update', $intern1->id), [
            'mode'                 => 'bulk',
            'ids'                  => "{$intern1->id},{$intern2->id}",
            'alumni_group_url'     => $groupUrl,
            'alumni_group_label'   => $groupLabel,
            'job_info_url'         => $jobUrl,
            'job_info_description' => 'Lowongan kerja',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify both interns have their InternExtra updated
        $this->assertDatabaseHas('intern_extras', [
            'intern_id'        => $intern1->id,
            'alumni_group_url' => $groupUrl,
            'job_info_url'     => $jobUrl,
        ]);

        $this->assertDatabaseHas('intern_extras', [
            'intern_id'        => $intern2->id,
            'alumni_group_url' => $groupUrl,
            'job_info_url'     => $jobUrl,
        ]);

        // Cleanup
        InternExtra::whereIn('intern_id', [$intern1->id, $intern2->id])->delete();
        $intern1->delete();
        $intern2->delete();
        $u1->delete();
        $u2->delete();
        $admin->delete();
    }
}
