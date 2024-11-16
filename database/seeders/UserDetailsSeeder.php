<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserDetails;

class UserDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo thông tin chi tiết người dùng mẫu
        UserDetails::create([
            'user_id' => 1, // Liên kết với user_id của Toàn đẹp trai VIP promax
            'phone' => '0123456789',
            'address' => '123 Đường ABC, Hà Nội',
            // 'email' => 'toanvip@example.com',
            'profile_picture' => 'toan.jpg',
            'gender' => 'male',
            'dob' => '2000-01-01',
        ]);

        UserDetails::create([
            'user_id' => 2, // Liên kết với user_id của Hiệp đẹp trai
            'phone' => '0987654321',
            'address' => '456 Đường XYZ, Hồ Chí Minh',
            // 'email' => 'hiepvip@example.com',
            'profile_picture' => 'hiep.jpg',
            'gender' => 'male',
            'dob' => '1998-05-10',
        ]);

        UserDetails::create([
            'user_id' => 3, // Liên kết với user_id của Đạt đẹp trai
            'phone' => '0112233445',
            'address' => '789 Đường PQR, Đà Nẵng',
            // 'email' => 'datvip@example.com',
            'profile_picture' => 'dat.jpg',
            'gender' => 'male',
            'dob' => '1999-08-15',
        ]);
        UserDetails::create([
            'user_id' => 4, // Liên kết với user_id của Đạt đẹp trai
            'phone' => '0112233445',
            'address' => '789 Đường PQR, Đà Nẵng',
            // 'email' => 'datvip@example.com',
            'profile_picture' => 'dat.jpg',
            'gender' => 'male',
            'dob' => '1999-08-15',
        ]);
    }
}
