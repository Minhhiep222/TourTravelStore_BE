<?php

namespace App\Http\Controllers;
use App\Models\Favorite;
use App\Models\User;
use App\Models\Tour;
use App\Models\HashSecret;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function addTourToFavorite(Request $request) {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'tour_id' => 'required',
            ], [
                'user_id.required' => 'You must be logged in to add to favorites.',
                'user_id.exists' => 'User not found',
                'tour_id.required' => 'Tour is required',
                'tour_id.exists' => 'Tour not found',
            ]);
    
            $encodedTourId = $validatedData['tour_id'];
            $tourId = HashSecret::decrypt($encodedTourId); 
    
            if (!$tourId) {
                return response()->json([
                    "message" => "Invalid tour ID.",
                ], 404);
            }
    
          
            $favorite = Favorite::create([
                'user_id' => $validatedData['user_id'],
                'tour_id' => $tourId,  
            ]);
    
            if ($favorite) {
                return response()->json([
                    "message" => "Tour added to favorites list successfully."
                ], 200);
            } else {
                return response()->json([
                    "message" => "Adding tour to favorites failed."
                ], 400);
            }
        } catch (ValidationException $e) {
            return response()->json([
                "message" => "Đã xảy ra lỗi",
                "error" => $e->getMessage() 
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "An unexpected error occurred.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
    public function getToursFavorite(Request $request)
    {
        // $key = 'dat123';
        try {
          
            $validatedData = $request->validate([
                'user_id' => 'required',
                'page' => 'integer|min:1',
            ], [
                'user_id.required' => 'User ID is required',
                'page.integer' => 'Invalid page number',
                'page.min' => 'Number of pages must not be less than 1',
            ]);
            $favoriteTours = Favorite::where('user_id', $validatedData['user_id'])
                ->pluck('tour_id')
                ->toArray();
            $dataFavorite = Tour::with('images')
                ->whereIn('id', $favoriteTours)
                ->paginate(3);
   
            $dataFavorite->getCollection()->transform(function($data){
                return [
                    'id' => HashSecret::encrypt($data->id),
                    'name' => $data->name,
                    'description' => $data->description,
                    'duration' => $data->duration,
                    'location' => $data->location,
                    'price' => $data->price,
                    'price_children' => $data->price_children,
                    'availability' => $data->availability,
                    'end_date' => $data->end_date,
                    'created_at' => $data->created_at,
                    'start_date' => $data->start_date,
                    'updated_at' => $data->updated_at,
                    'images' => $data->images,
                ];
            });
    
            return response()->json([
                "message" => 'Get tours successful',
                "data" => $dataFavorite,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "An unexpected error occurred.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
    public function deleteFavorite(Request $request) {
      
        try {
            $validatedData = $request->validate([
                'tour_id' => 'required',
                'user_id' => 'required|exists:users,id',
            ], [
                'tour_id.required' => 'Tour is required',
                'user_id.required' => 'You must be logged in to remove to favorites.',
                'user_id.exists' => 'User not found',
            ]);
    
          
            $encodedTourId = $validatedData['tour_id'];
            $tour_id = HashSecret::decrypt($encodedTourId);
    
         
            if (!$tour_id) {
                return response()->json([
                    "error" => "Invalid tour ID.",
                ], 404);
            }
    
          
            $deleteTour = Favorite::where('tour_id', $tour_id)->delete();
    
          
            if ($deleteTour === 0) {
                return response()->json([
                    "error" => "No favorite tour found for this user.",
                    // "data" => $tour_id,
                ], 200);
            }
            return response()->json([
                "message" => 'Delete tour favorite successful',
                "data" => $deleteTour,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "An unexpected error occurred.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
 
}
