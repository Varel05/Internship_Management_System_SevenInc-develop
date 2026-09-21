<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminProfileUsernameTest extends TestCase
{
    public function test_admin_can_update_username_in_profile(): void
    {
        $admin = User::create([
            'name' => 'admin_test_' . time(),
            'email' => 'admin_test_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $newUsername = 'admin_updated_' . time();

        $response = $this->actingAs($admin)
            ->put(route('admin.profile.update'), [
                'username' => $newUsername,
                'email' => $admin->email,
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => $newUsername,
        ]);

        // Cleanup
        $admin->delete();
    }

    public function test_admin_cannot_use_already_taken_username(): void
    {
        $existingUsername = 'other_user_' . time();
        User::create([
            'name' => $existingUsername,
            'email' => 'other_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $admin = User::create([
            'name' => 'admin_orig_' . time(),
            'email' => 'admin_orig_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.profile.edit'))
            ->put(route('admin.profile.update'), [
                'username' => $existingUsername,
                'email' => $admin->email,
            ]);

        $response->assertSessionHasErrors('username');

        // Cleanup
        User::where('name', $existingUsername)->delete();
        $admin->delete();
    }
}
