<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo hoặc cập nhật nếu username đã tồn tại
        User::updateOrCreate(
            ['username' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('Dat72@@##!!aa'),
                'role' => 3,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['username' => 'user1@example.com'],
            [
                'name' => 'Normal User',
                'email' => 'user1@example.com',
                'password' => Hash::make('Dat72@@##!!aa'),
                'role' => 1,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['username' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'password' => Hash::make('Dat72@@##!!aa'),
                'role' => 2,
                'email_verified_at' => now(),
            ]
        );

    }
}