<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        if (!$admin) {
            User::create([
                'name' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'), // default password
                'role' => 'admin', // pastikan ada kolom role di tabel users
            ]);
        } else if (!$admin->name) {
            $admin->update(['name' => 'admin']);
        }
    }
}
