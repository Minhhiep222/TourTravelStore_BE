<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'contact_id' => 1,
            'name' => 'Nguyễn Văn Huy',
            'email' => 'nguyenvanhuy@example.com',
            'gender' => 'male',
            'dob' => '1985-07-10',
            'type_customer' => 'self',
            'nationality' => 'Vietnam',
            'passport_number' => 'A1234567',
        ]);

        Customer::create([
            'contact_id' => 2,
            'name' => 'Nguyễn Thành Đạt',
            'email' => 'datvuive123@example.com',
            'gender' => 'male',
            'dob' => '1990-05-15',
            'type_customer' => 'self',
            'nationality' => 'Vietnam',
            'passport_number' => 'B7654321',
        ]);
        Customer::create([
            'contact_id' => 3,
            'name' => 'Nguyễn Minh Hiệp',
            'email' => 'Nguyenminhhiep@example.com',
            'gender' => 'male',
            'dob' => '1990-05-15',
            'type_customer' => 'self',
            'nationality' => 'Vietnam',
            'passport_number' => 'B7654321',
        ]);
        Customer::create([
            'contact_id' => 4,
            'name' => 'Nuyễn Đức Toàn',
            'email' => 'nhoctoan6666@gmail.com',
            'gender' => 'male',
            'dob' => '1990-05-15',
            'type_customer' => 'self',
            'nationality' => 'Vietnam',
            'passport_number' => 'B7654321',
        ]);
    
    }
}
