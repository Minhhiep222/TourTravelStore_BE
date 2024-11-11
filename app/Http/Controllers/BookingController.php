<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\HashSecret;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function BookTour(Request $request) {
        try {
            $validatedData = $request->validate([
                'tour_id' => 'required',
                'customer_id' => 'required',
                'booking_date' => 'required',
                'number_of_people' => 'required',
                'user_id' => 'required',
                'tour_guide_id' => 'required',
            ], [
                'tour_id.unique' => 'co roi',
                'tour_id.required' => 'Account name is required.',
            ]);
        } catch(error) {

        }
    }

    /**
     * Find booking with tour_id
     * @param mixed $hashId
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($hashId) {
        try {
            // $id = HashSecret::decrypt($hashId);
            $booking = Booking::where('id',$hashId)->with('tour')->firstOr();

            if(!$booking) {
                return response()->json([
                    "message" => "Booking Not Found"
                ], 404);
            };

            $price = $booking->number_of_people * $booking->tour->price;

            return response()->json([
                "booking" => $booking,
                "price" => $price,
                "tour_id" => $booking->tour->id
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'message' => "Some thing wrong",
                "error" => $e->getMessage()
            ], 500);

        }
    }

}