<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Events\TourCreated;
use App\Models\Tour;
use App\Models\User;
use App\Models\NotificationTour;    
use App\Models\HashSecret;


class NotificationController extends Controller
{
   
    public function getNotification(Request $request) {
        try {
           
            $validatedData = $request->validate([
                'user_id' => 'required', 
            ], [
                'user_id.required' => 'user_id is required',
            ]);
    
         
            $notifications = NotificationTour::with(['tour.images'])
                ->where('user_id', $validatedData['user_id'])
                ->get();
    
           
                $notifications->transform(function ($notification) {
                    $notification->tour->encrypt_id = HashSecret::encrypt($notification->tour->id);
                    return $notification;
                });
                
            // Count unread notifications
            $unreadCount = $notifications->where('read', 0)->count();
    
            // Return the response with encrypted tour ids
            return response()->json([
                'message' => 'Get notifications successfully',
                'data' => $notifications,
                'notRead' => $unreadCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function readNotification(Request $request){
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'user_id' => 'required', 
                'tour_id' => 'required',
            ], [
                'user_id.required' => 'user_id is required',
                'tour_id.required' => 'tour_id is required', 
            ]);
            
         
            $notification = NotificationTour::where('user_id', $validatedData['user_id'])
                ->where('tour_id', HashSecret::decrypt($validatedData['tour_id']))
                ->update(['read' =>true]);
                
            
        
            return response()->json([
                'message' => 'Get notification successfully',
                // 'data' => $notification,
                'tour_id' => $validatedData['tour_id'],
                'user_id' => $validatedData['user_id'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "An unexpected error occurred.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
    
    public function turnOffNotification(Request $request) {
        try {
            // Validate user_id
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
            ], [
                'user_id.required' => 'user_id is required',
                'user_id.exists' => 'The user does not exist',
            ]);
    
           
            $user = User::find($request->user_id);
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
    
          
            if ($user->notication == 0) {  
                $user->notication = 1;
                $user->save();
            } else if($user->notication == 1) {
                $user->notication = 0;
                $user->save();
            }
    
            return response()->json([
                'message' => 'Notification turned off successfully.',
                'data' => $user,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                "message" => "An unexpected error occurred.",
                "error" => $e->getMessage(),
            ], 500);
        }
    }
    
    public function checkNotifyUser(Request $request) {
        try {
            // Validate user_id
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
            ], [
                'user_id.required' => 'user_id is required',
                'user_id.exists' => 'The user does not exist',
            ]);
            $user = User::find($validatedData['user_id']); 
            if($user->notication == 1) {
                $data = true;
            } else {
                $data = false;
            }
            return response()->json([
                'message' => 'Notification turned off successfully.',
                'data' => $data,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                "message" => "An unexpected error occurred.",
                "error" => $e->getMessage(),
            ], 500);
        }
    }
   

   
        public function seenAllNotification(Request $request)
        {
            // Xác thực đầu vào
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',  
            ], [
                'user_id.required' => 'user_id is required',  
                'user_id.exists' => 'The user does not exist',  
            ]);
            
            
           $data = NotificationTour::where('user_id', $validatedData['user_id'])
                ->where('read', 0)  
                ->update(['read' => 1]); 
    
            return response()->json([
                'message' => 'All notifications marked as read.',
                'data' => $data
            ]);
        }
    }
    

