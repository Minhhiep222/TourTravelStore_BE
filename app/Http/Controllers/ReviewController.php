<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\HashSecret;
use App\Models\ImageReview;
use Storage;
use File;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    //
    public function index(Request $request) {
        try {

            $tour_id = HashSecret::decrypt($request->tour_id);
            // Lấy số lượng Review mỗi trang từ request
            $perPage = $request->input('per_page', 10);

            // Lấy review theo tour id
            $reviews = Review::with('image_reviews', 'user')
            ->where('tour_id', $tour_id)
            ->orderBy('updated_at', 'desc')->paginate($perPage);

            // Khởi tạo truy vấn

            // Tạo mảng Review tùy chỉnh để trả về
            $reviewsArray = $reviews->getCollection()->map(function ($review) {
                return [
                    'id' => HashSecret::encrypt($review->id),
                    "user_id" => $review->user_id,
                    "tour_id" => HashSecret::encrypt($review->tour_id),
                    "rating" => $review->rating,
                    "comment" => $review->comment,
                    "updated_at" => $review->updated_at,
                    "status" => $review->status,
                    "parent_id" => $review->parent_id,
                    "image_reviews" => $review->image_reviews,
                    "user" => $review->user
                ];
            });

            return response()->json([
                'reviews' => $reviewsArray,
                'links' => [
                    'next' => $reviews->nextPageUrl(),
                    'prev' => $reviews->previousPageUrl(),
                ],
                'meta' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
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
           //Make vaildate for variable
            $validatedData = $request->validate([
                'rating' => 'required|integer',
                'user_id' => 'required|integer',
                'comment' => 'required|string',
                'images' => 'nullable'
            ]);

            $tour_id = HashSecret::decrypt($request->tour_id);

            $validatedData['tour_id'] = $tour_id;

            $review = Review::create($validatedData);

             // Handle file uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = time() . '_' . $image->getClientOriginalName();
                    Storage::disk('public')->put($path, File::get($image));
                    $image = ImageReview::create([
                        'review_id' => $review->id,
                        'image_url' => $path,
                        'alt_text' => $request->input('alt_text', 'Default alt text'),
                    ]);
                    // http://127.0.0.1:8000/images/7B9dDErH16ywJWIhieXV9sRYitUb0dC5qNgJ0jCo.png
                }
            }

           return response()->json([
               'message' => "reviews successfully created",
               'review' => $review,
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


    public function show($hashId)
    {
        try {
            //Decrypt id
            $id = HashSecret::decrypt($hashId);
            //
            $review = review::with('image_reviews', 'user')->find($id);
            //Check if review not exits
            if (!$review) {
                return response()->json([
                    "message" => "review not found " ,
                ], 404);
            }

            //Return when find the review
            return response()->json([
                "review" => $review,
                "id" => HashSecret::encrypt($review->id) // Updated to encrypt the review ID
            ], 200);
        }catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Xử lý trường hợp giải mã không thành công
            return response()->json([
                "message" => "review Not Found",
                "error" => $e->getMessage(),
            ], 400); // 400 Bad Request
        } catch (\Exception $e) {
            return response()->json([
                "message"=> "Something Went Wrong",
                "error"=> $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $hashId)
    {
        try {
            $id = HashSecret::decrypt($hashId); // Decrypt the hash ID
            $review = Review::with('image_reviews', 'user')->find($id);
            // Check if review exists
            if (!$review) {
                return response()->json([
                    'message' => "Review not found",
                ], 404);
            }

            // Validate data
            $validatedData = $request->validate([
                'user_id' => 'required|integer',
                'rating' => 'required|integer',
                'comment' => 'required|string',
            ]);

            // Update review
            $review->update($validatedData);
            $uploadedImages = [];
            // Handle file uploads
            if ($request->hasFile('images')) {
                //Delete review exitting
                $images = $review->image_reviews;
                foreach($images as $image){
                    $filePath = public_path('/images/' . $image->image_url);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    $image->delete();
                }
                // Upload new images
                foreach ($request->file('images') as $image) {
                    $path = time() . '_' . $image->getClientOriginalName();
                    Storage::disk('public')->put($path, File::get($image));
                    $uploadedImages[] =  $image;
                    ImageReview::create([
                        'review_id' => $review->id,
                        'image_url' => $path,
                        'alt_text' => $request->input('alt_text', 'Default alt text'),
                    ]);
                }
            }

            return response()->json([
                'message' => "review successfully updated",
                'review' => $review,
                'image' => $uploadedImages,
                'id' => HashSecret::encrypt($review->id), // Updated to encrypt the review ID
            ], 200);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error updating review: ' . $e->getMessage());

            return response()->json([
                'message' => "Something went wrong",
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($hashId)
    {
        try {
            $id = HashSecret::decrypt($hashId); // Decrypt the hash ID
            $review = Review::with('image_reviews')->find($id);
            // Check if Review exists
            if (!$review) {
                return response()->json([
                    "message" => "Review not found",
                ], 404);
            }

            // Delete Review
            foreach( $review->image_reviews as $image) {
                $image->delete();
            }
            $review->delete();

            return response()->json([
                "message" => "Destroy successfully",
            ], 200);
        } catch (\Exception $e) {
            // Write bug on file log
            Log::error("Error destroy Review". $e->getMessage());
            return response()->json([
                "message" => "Something Went Wrong",
                "error" =>  $e->getMessage()
            ], 500);
        }
    }


}
