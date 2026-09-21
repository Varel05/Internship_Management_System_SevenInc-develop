<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use App\Models\InternshipRegistration;
use App\Models\AlumniMembercard;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandMembercardBackgroundTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createAdmin(): User
    {
        return User::create([
            'name'     => 'admin_' . uniqid(),
            'email'    => 'admin_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);
    }

    private function createPemagang(): User
    {
        return User::create([
            'name'     => 'pemagang_' . uniqid(),
            'email'    => 'pemagang_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role'     => 'pemagang',
        ]);
    }

    public function test_admin_can_upload_membercard_background_when_creating_brand(): void
    {
        $admin = $this->createAdmin();

        $file = UploadedFile::fake()->image('membercard_bg.jpg', 1011, 638);
        $code = 'TB_' . strtoupper(substr(uniqid(), -4));

        $response = $this->actingAs($admin)->post(route('admin.brands.store'), [
            'code' => $code,
            'name' => 'Test Brand Premium',
            'membercard_bg' => $file,
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $this->assertDatabaseHas('brands', [
            'code' => $code,
            'name' => 'Test Brand Premium',
        ]);

        $brand = Brand::where('code', $code)->first();
        $this->assertNotNull($brand->membercard_bg);
        Storage::disk('public')->assertExists($brand->membercard_bg);
    }

    public function test_admin_can_update_membercard_background_and_old_file_is_deleted(): void
    {
        $admin = $this->createAdmin();

        $oldFile = UploadedFile::fake()->image('old_bg.jpg', 1011, 638);
        $code = 'UB_' . strtoupper(substr(uniqid(), -4));
        $brand = Brand::create([
            'code' => $code,
            'name' => 'Update Brand',
            'membercard_bg' => $oldFile->store('brands', 'public'),
        ]);

        $oldPath = $brand->membercard_bg;
        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('new_bg.png', 1011, 638);

        $response = $this->actingAs($admin)->put(route('admin.brands.update', $brand->id), [
            'code' => $code,
            'name' => 'Update Brand Edited',
            'membercard_bg' => $newFile,
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $brand->refresh();

        $this->assertNotEquals($oldPath, $brand->membercard_bg);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($brand->membercard_bg);
    }

    public function test_brand_detail_page_renders_membercard_preview(): void
    {
        $admin = $this->createAdmin();

        $brand = Brand::create([
            'code' => 'SI_' . strtoupper(substr(uniqid(), -3)),
            'name' => 'Seven Inc',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.brands.show', $brand->id));
        $response->assertStatus(200);
        $response->assertSee('Preview Membercard Digital');
        $response->assertSee('Seven Inc');
        $response->assertSee('ALUMNI MEMBER');
    }

    private function createIntern($user, $brand, $fullname): InternshipRegistration
    {
        $city = \App\Models\City::first() ?? \App\Models\City::create(['name' => 'Yogyakarta']);
        $inst = \App\Models\Institution::first() ?? \App\Models\Institution::create(['name' => 'Universitas Indonesia', 'type' => 'Universitas']);
        $fac  = \App\Models\Faculty::first() ?? \App\Models\Faculty::create(['name' => 'Fakultas Teknik', 'institution_id' => $inst->id]);
        $sp   = \App\Models\StudyProgram::first() ?? \App\Models\StudyProgram::create(['name' => 'Informatika', 'faculty_id' => $fac->id]);
        $div  = \App\Models\Division::first() ?? \App\Models\Division::create(['name' => 'Web Development', 'code' => 'WD']);

        return InternshipRegistration::create([
            'user_id'                => $user->id,
            'fullname'               => $fullname,
            'born_date'              => '2000-01-01',
            'gender'                 => 'Laki-laki',
            'phone_number'           => '08123456789',
            'city_id'                => $city->id,
            'student_id'             => '12345678',
            'institution_id'         => $inst->id,
            'faculty_id'             => $fac->id,
            'study_program_id'       => $sp->id,
            'division_id'            => $div->id,
            'brand_id'               => $brand->id,
            'internship_reason'      => 'Ingin menambah pengalaman',
            'internship_type'        => 'PKL',
            'internship_arrangement' => 'Onsite',
            'current_status'         => 'Mahasiswa/Pelajar',
            'english_book_ability'   => 'Saya bisa',
            'start_date'             => '2026-01-01',
            'end_date'               => '2026-04-01',
            'internship_status'      => InternshipRegistration::STATUS_COMPLETED,
        ]);
    }

    public function test_pemagang_can_view_redesigned_membercard_with_brand_theme(): void
    {
        $user = $this->createPemagang();

        $brand = Brand::create([
            'code' => 'ALB_' . strtoupper(substr(uniqid(), -3)),
            'name' => 'Alona',
        ]);

        $reg = $this->createIntern($user, $brand, 'Budi Santoso');

        $memberCode = 'ALB' . rand(1000, 9999);
        $membercard = AlumniMembercard::create([
            'intern_id'   => $reg->id,
            'member_code' => $memberCode,
            'batch_year'  => '2026',
        ]);

        $response = $this->actingAs($user)->get(route('pemagang.membercard'));
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee($memberCode);
        $response->assertSee('Alona');
        $response->assertSee('ALUMNI MEMBER');
    }

    public function test_admin_membercard_show_renders_redesigned_card(): void
    {
        $admin = $this->createAdmin();

        $brand = Brand::create([
            'code' => 'SI_' . strtoupper(substr(uniqid(), -3)),
            'name' => 'Seven Inc',
        ]);

        $reg = $this->createIntern($admin, $brand, 'Citra Kirana');

        $memberCode = 'SI' . rand(1000, 9999);
        $membercard = AlumniMembercard::create([
            'intern_id'   => $reg->id,
            'member_code' => $memberCode,
            'batch_year'  => '2026',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.membercards.show', $memberCode));
        $response->assertStatus(200);
        $response->assertSee('Citra Kirana');
        $response->assertSee($memberCode);
        $response->assertSee('Seven Inc');
        $response->assertSee('ALUMNI MEMBER');
    }
}
