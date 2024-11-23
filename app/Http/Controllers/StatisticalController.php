<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Customer;
use Carbon\Carbon;

class StatisticalController extends Controller
{
    public function getStatistics($timeframe)
    {
        $data = [];

        // Xác định khoảng thời gian
        switch ($timeframe) {
            case 'day':
                $startDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                break;
            default:
                return response()->json(['error' => 'Invalid timeframe'], 400);
        }

        // Lấy tổng doanh thu
        $data['revenue'] = Booking::where('booking_date', '>=', $startDate)
            ->sum('total_price');

        // Số lượng khách mới
        $data['newCustomers'] = Customer::where('created_at', '>=', $startDate)
            ->count();

        // Số lượng tour đã đặt
        $data['toursBooked'] = Booking::where('booking_date', '>=', $startDate)
            ->count();

        // Các giao dịch gần đây
        $data['transactions'] = Booking::where('booking_date', '>=', $startDate)
            ->with('customer:id,name') // Giả sử có quan hệ customer để lấy tên khách
            ->orderBy('booking_date', 'desc')
            ->take(5)
            ->get(['booking_date', 'customer_id', 'total_price']);

        // Định dạng cho đúng yêu cầu giao diện
        $data['transactions'] = $data['transactions']->map(function ($transaction) {
            return [
                'date' => Carbon::parse($transaction->booking_date)->format('Y-m-d'), // Đảm bảo là đối tượng Carbon
                'customer' => $transaction->customer->name ?? 'N/A',
                'amount' => number_format($transaction->total_price) . ' VND',
                'status' => 'Hoàn Thành', // Có thể tùy chỉnh theo logic của bạn
            ];
        });

        return response()->json($data);
    }
}
