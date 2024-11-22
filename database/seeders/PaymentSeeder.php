<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payments')->insert([
            [
                'tour_id' => 1,
                'number_of_tickers' => 2,
                'total_price' => 5000,
                'user_id' => 1,
                'payment_method' => 'transfer',
                'status' => 'completed',
                'notes' => 'Thanh toán qua chuyển khoản',
                'transaction_id' => 'TX123456',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tour_id' => 2,
                'number_of_tickers' => 1,
                'total_price' => 3000,
                'user_id' => 2,
                'payment_method' => 'cash',
                'status' => 'pending',
                'notes' => 'Thanh toán bằng tiền mặt',
                'transaction_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tour_id' => 3,
                'number_of_tickers' => 4,
                'total_price' => 10000,
                'user_id' => 3,
                'payment_method' => 'transfer',
                'status' => 'refunded',
                'notes' => 'Đã hoàn tiền cho khách hàng',
                'transaction_id' => 'TX654321',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    
        // Payment::factory()->count(50)->create(); // Creates 50 payment records

    }
}