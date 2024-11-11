<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\HashSecret;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    //
    public function index(Request $request) {
        try {
            // Lấy số lượng Review mỗi trang từ request
            $perPage = $request->input('per_page', 10);

            // Lấy tham số sắp xếp từ query string (mặc định sắp xếp theo giá)
            $sortBy = $request->query('sort', 'price');

            // Khởi tạo truy vấn
            $Reviews = Review::with('images', 'schedules');

            // Sắp xếp theo tiêu chí
            switch ($sortBy) {
                case 'price':
                    $Reviews->orderBy('price', 'desc'); // Sắp xếp theo giá giảm dần
                    break;
                case 'latest':
                    $Reviews->orderBy('created_at', 'desc'); // Sắp xếp theo thời gian tạo mới nhất
                    break;
                default:
                    return response()->json(['message' => 'Invalid sort parameter.'], 400);
            }

            // Phân trang kết quả
            $Reviews = $Reviews->paginate($perPage);

            // Tạo mảng Review tùy chỉnh để trả về
            $reviewsArray = $Reviews->getCollection()->map(function ($review) {
                return [
                    'id' => HashSecret::encrypt($review->id), // Mã hóa ID review
                    "user_id" => $review->user_id,
                    "entity_id" => $review->entity_id,
                    "rating" => $review->rating,
                    "comment" => $review->comment,
                    "status" => $review->status,
                    "parent_id" => $review->parent_id,
                    // "is_approved" => $review->is_approved,
                ];
            });

            return response()->json([
                'reviews' => $reviewsArray,
                'links' => [
                    'next' => $Reviews->nextPageUrl(),
                    'prev' => $Reviews->previousPageUrl(),
                ],
                'meta' => [
                    'current_page' => $Reviews->currentPage(),
                    'last_page' => $Reviews->lastPage(),
                    'per_page' => $Reviews->perPage(),
                    'total' => $Reviews->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
       try {
           // dd($request);
           $schedule = null;
           //Make vaildate for variable
           $validatedData = $request->validate([
               'user_id' => 'required|integer',
               'description' => 'required|string',
               'entity_id' => 'required|integer',
               'rating' => 'required|integer',
               // 'start_date' => 'required|date',
               // 'end_date' => 'required|date|after:start_date',
               'comment' => 'required|string',
               'status' => 'required',
               'parent_id' => 'nullable',
           ]);

           $reviews = Review::create($validatedData);

           return response()->json([
               'message' => "reviews successfully created",
               'reviews' => $reviews,
           ], 200);



       } catch (\Exception $e) {
           // Ghi lỗi vào file log
           Log::error('Error creating reviews: ' . $e->getMessage());

           return response()->json([
               'message' => "something really wrong",
               'error' => $e->getMessage()
           ], 500);
       }
    }



}