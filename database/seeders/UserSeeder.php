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
            ['username' => '    '],
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
                'password' => Hash::make('password123'),
                'role' => 2,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['username' => 'manager'],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'password' => Hash::make('Dat72@@##!!aa'),
                'role' => 3,
                'email_verified_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['username' => 'nguyenthanhdat123@gmail.com'],
            [
                'name' => 'Nguyen Thanh Dat',
                'email' => 'nguyenthanhdat123@gmail.com',
                'password' => Hash::make('Dat72@@##!!aa'),
                'role' => 1,
                'notication' => true,
                'email_verified_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['username' => 'nguyenthanhdat456@gmail.com'],
            [
                'name' => 'Thanh Dat Nguyen',
                'email' => 'nguyenthanhdat456@gmail.com',
                'password' => Hash::make('Dat72@@##!!aa'),
                'role' => 1,
                'notication' => true,
                'email_verified_at' => now(),
            ]
        );
        User::updateOrCreate(
            ['username' => 'nhoctoan6666@gmail.com'],
            [
                'name' => 'Duc Toan',
                'email' => 'nhoctoan6666@gmail.com',
                'password' => Hash::make('Ductoan3$'),
                'role' => 3,
                'email_verified_at' => now(),
            ]
        );

    }
}
