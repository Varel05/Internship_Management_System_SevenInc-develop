<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthUsernameTest extends TestCase
{
    public function test_user_can_register_with_username_saved_to_name_column(): void
    {
        $uniqueUsername = 'testuser_' . time();
        $uniqueEmail = 'testuser_' . time() . '@example.com';

        $response = $this->post(route('user.register.submit'), [
            'username' => $uniqueUsername,
            'email' => $uniqueEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('pemagang.registration.form'));

        $this->assertDatabaseHas('users', [
            'name' => $uniqueUsername,
            'email' => $uniqueEmail,
        ]);

        $this->assertAuthenticated();

        // Clean up
        User::where('email', $uniqueEmail)->delete();
    }

    public function test_cannot_register_with_duplicate_username(): void
    {
        $uniqueUsername = 'dupuser_' . time();
        $email1 = 'dup1_' . time() . '@example.com';
        $email2 = 'dup2_' . time() . '@example.com';

        User::create([
            'name' => $uniqueUsername,
            'email' => $email1,
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->from(route('user.register'))->post(route('user.register.submit'), [
            'username' => $uniqueUsername,
            'email' => $email2,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');

        // Clean up
        User::where('name', $uniqueUsername)->delete();
    }

    public function test_user_can_login_using_username(): void
    {
        $uniqueUsername = 'loginuser_' . time();
        $uniqueEmail = 'loginuser_' . time() . '@example.com';

        User::create([
            'name' => $uniqueUsername,
            'email' => $uniqueEmail,
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->post(route('user.login.submit'), [
            'login' => $uniqueUsername,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pemagang.dashboard'));
        $this->assertAuthenticated();

        // Clean up
        User::where('email', $uniqueEmail)->delete();
    }

    public function test_user_can_login_using_email(): void
    {
        $uniqueUsername = 'emailuser_' . time();
        $uniqueEmail = 'emailuser_' . time() . '@example.com';

        User::create([
            'name' => $uniqueUsername,
            'email' => $uniqueEmail,
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->post(route('user.login.submit'), [
            'login' => $uniqueEmail,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pemagang.dashboard'));
        $this->assertAuthenticated();

        // Clean up
        User::where('email', $uniqueEmail)->delete();
    }
}
