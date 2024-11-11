<?php

namespace App\Http\Controllers;
use App\Models\Favorite;
use App\Models\User;

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
    
            $key = 'dat123';
            $encodedTourId = $validatedData['tour_id'];
            $user = new User(); 
            $tourId = $user->decryptId($encodedTourId, $key); 
    
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
                ], 201);
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
    
}
