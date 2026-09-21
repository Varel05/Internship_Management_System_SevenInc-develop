<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PemagangSettingsTest extends TestCase
{
    public function test_pemagang_can_view_settings_page_with_user_table_fields_only(): void
    {
        $user = User::create([
            'name' => 'pemagang_view_' . time(),
            'email' => 'pemagang_view_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pemagang',
        ]);

        $response = $this->actingAs($user)->get(route('pemagang.settings'));

        $response->assertStatus(200);
        $response->assertSee('name="username"', false);
        $response->assertSee('name="email"', false);
        $response->assertDontSee('name="phone_number"', false);
        $response->assertDontSee('name="profile_picture"', false);

        $user->delete();
    }

    public function test_pemagang_can_update_username_and_email(): void
    {
        $user = User::create([
            'name' => 'pemagang_orig_' . time(),
            'email' => 'pemagang_orig_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pemagang',
        ]);

        $newUsername = 'pemagang_new_' . time();
        $newEmail = 'pemagang_new_' . time() . '@example.com';

        $response = $this->actingAs($user)->put(route('pemagang.settings.update'), [
            'username' => $newUsername,
            'email' => $newEmail,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $newUsername,
            'email' => $newEmail,
        ]);

        $user->delete();
    }

    public function test_pemagang_cannot_use_duplicate_username(): void
    {
        $existingUser = User::create([
            'name' => 'existing_pemagang_' . time(),
            'email' => 'existing_pemagang_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pemagang',
        ]);

        $user = User::create([
            'name' => 'target_pemagang_' . time(),
            'email' => 'target_pemagang_' . time() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pemagang',
        ]);

        $response = $this->actingAs($user)
            ->from(route('pemagang.settings'))
            ->put(route('pemagang.settings.update'), [
                'username' => $existingUser->name,
                'email' => $user->email,
            ]);

        $response->assertSessionHasErrors('username');

        $existingUser->delete();
        $user->delete();
    }
}
