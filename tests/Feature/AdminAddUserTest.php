<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminAddUserTest extends TestCase
{
    public function test_admin_can_view_create_user_page(): void
    {
        $admin = User::create([
            'name' => 'admin_v_' . time(),
            'email' => 'admin_v_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.create'));
        $response->assertStatus(200);
        $response->assertSee('Tambah Admin Baru');
        $response->assertSee('type="hidden" name="role" value="admin"', false);

        $admin->delete();
    }

    public function test_admin_can_add_new_admin(): void
    {
        $admin = User::create([
            'name' => 'admin_add_' . time(),
            'email' => 'admin_add_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $newUsername = 'new_admin_' . time();
        $newEmail = 'new_admin_' . time() . '@example.com';

        $response = $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name'                  => $newUsername,
                'email'                 => $newEmail,
                'role'                  => 'admin',
                'password'              => 'adminpass123',
                'password_confirmation' => 'adminpass123',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'  => $newUsername,
            'email' => $newEmail,
            'role'  => 'admin',
        ]);

        // Verify that the newly created admin can log in with username
        $loginResponse = $this->post(route('user.login.submit'), [
            'login'    => $newUsername,
            'password' => 'adminpass123',
        ]);
        $loginResponse->assertRedirect(route('admin.dashboard.index'));

        // Clean up
        User::where('name', $newUsername)->delete();
        $admin->delete();
    }

    public function test_cannot_add_user_with_duplicate_username_or_email(): void
    {
        $admin = User::create([
            'name' => 'admin_dup_' . time(),
            'email' => 'admin_dup_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $existing = User::create([
            'name' => 'existing_u_' . time(),
            'email' => 'existing_u_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Coba tambah dengan username yang sudah ada
        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name'                  => $existing->name,
                'email'                 => 'other_' . time() . '@example.com',
                'role'                  => 'admin',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertSessionHasErrors('name');

        // Clean up
        $existing->delete();
        $admin->delete();
    }
}
