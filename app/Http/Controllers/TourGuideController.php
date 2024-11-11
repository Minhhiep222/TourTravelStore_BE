<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourGuide;
use Illuminate\Validation\ValidationException;

class TourGuideController extends Controller
{
    // private function encryptId($id, $key) {
    //     $method = 'AES-256-CBC';
    //     $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
    //     $encryptedId = openssl_encrypt($id, $method, $key, 0, $iv);
    //     return base64_encode($iv . $encryptedId);
    // }
    
    // private function decryptId($encryptedId, $key) {
    //     $method = 'AES-256-CBC';
    //     $decodedUrl = urldecode($encryptedId);
    //     $decodedData = base64_decode($decodedUrl);
    //     $ivLength = openssl_cipher_iv_length($method);
    //     $iv = substr($decodedData, 0, $ivLength);
    //     $encryptedIdWithoutIv = substr($decodedData, $ivLength);
        
    //     return openssl_decrypt($encryptedIdWithoutIv, $method, $key, 0, $iv);
    // }
    // private function isValidEmail($username)
    // {
    //     return filter_var($username, FILTER_VALIDATE_EMAIL);
    // }
    // public function isValidPhoneNumber($phone)
    // {
    //     if (substr($phone, 0, 1) === '+') {
    //         if (strlen($phone) != 12 || substr($phone, 3, 1) == '0' || !preg_match('/^\d+$/', substr($phone, 1))) {
    //             return false;
    //         }
    //         return true;
    //     } else {
    //         if (strlen($phone) != 10 || !preg_match('/^\d+$/', $phone) || $phone[0] != '0') {
    //             return false;
    //         }
    //         return true;
    //     }
    // }

    function getAllTourGuide(Request $request) {
      try {
        // $tourGuide = TourGuide::all();
        $key = 'dat123';
        // $encryptedTours = $tourGuide->map(function($tour) use ($key) {
        //     return [
        //         'id' => $this->encryptId($tour->id, $key),
        //         'name' => $tour->name, 
        //         'email' => $tour->email, 
        //         'phone' => $tour->phone, 
        //         'experience' => $tour->experience, 
        //     ];
        // });
        $tourGuideModel = new TourGuide();
        $encryptedTours = $tourGuideModel->getAllEncryptedTourGuides($key); 
        if($encryptedTours->isEmpty()) {
            return response()->json([
                "error" => "Tour guide not found",
            ], 404);
        } else {
            return response()->json([
                "message" => "Get a list of successful tour guides",
                 "data" => $encryptedTours
            ], 201);
        }
      }catch (QueryException $e) {
        return response()->json([
            "message" => "Database query error",
            "error" => $e->getMessage()
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            "message" => "An unknown error occurred",
            "error" => $e->getMessage()
        ], 500);
     }
    }
    function addTourGuide(Request $request) {
        try {
            // $validatedData = $request->validate([
            //     'email' => 'required|string|max:255|min:10|regex:/^\S*$/|unique:tour_guides,email',
            //     'name' => 'required|string|max:255|min:5|regex:/^[\p{L}\s]+$/u',
            //     'phone' => 'required|min:10|unique:tour_guides,phone',
            //     'experience' => 'required|min:0|numeric',
            // ], [
            //     'email.required' => 'Account name is required.',
            //     'email.max' => 'Account name must be between 10 and 255 characters.',
            //     'email.min' => 'Account name must be between 10 and 255 characters.',
            //     'email.regex' => 'email cannot contain spaces.',
            //     'email.unique' => 'this email already exits in the system',
            //     'name.required' => 'Name is required.',
            //     'name.max' => 'Account name must be between 5 and 255 characters.',
            //     'name.min' => 'Account name must be between 5 and 255 characters.',
            //     'name.regex' => 'Name can only contain letters and spaces, and cannot include special characters.',
            //     'phone.required' => 'phone is required.',
            //     // 'phone.regex' => 'Phone number can only contain numbers.',
            //     'experience.required' => 'Experience is required.',
            //     'experience.numeric' => 'Experience must be a number.',
            //     'experience.min' => 'Experience must be at least 0.'
            // ]);
            // if (!$this->isValidEmail($validatedData['email'])) {
            //     return response()->json([
            //         'message' => 'Validation failed',
            //         'errors' => [
            //             'email' => ['Email must be formatted as follows: abc@gmail.com.']
            //         ],
            //     ], 422);
            // };
                // if (!$this->isValidPhoneNumber($validatedData['phone'])) {
                //     return response()->json([
                //         'message' => 'Validation failed',
                //         'errors' => [
                //             'phone' => ['Phone number must be formatted as follows: 0123456789 or +84123456789.'],
                //         ],
                //     ], 422);
                // }
                // $phone = $validatedData['phone'];
                // $phoneNumber = '0' . substr($phone, 3);
                // $phoneNumber2 = '+84' . substr($phone, 1);
                // $tourGuides = TourGuide::all();

            //     foreach ($tourGuides as $tourGuide) {
            //         if ($tourGuide->phone == $phoneNumber || $tourGuide->phone == $phoneNumber2) {
            //             return response()->json([
            //                 'message' => 'Validation failed',
            //                 'errors' => [
            //                     'phone' => ['Phone number already exists.']
            //                 ],
            //             ], 422);
            //         }    
            // }

            // $tourGuide = tourGuide::create([
            //     'name' => $validatedData['name'],
            //     'email' => $validatedData['email'],
            //     'phone' => $validatedData['phone'],
            //     'experience' => $validatedData['experience'],
            // ]);
            $tourGuide = new TourGuide();
            $newTourGuide = $tourGuide->addTourGuide($request->all());
            if ($newTourGuide) {
                return response()->json([
                    "message" => "Create successful tour guide",
                    "data" => $newTourGuide
                ], 201);  
            } else {
                return response()->json([
                    "errors" => [
                        'errors' => ["Tour guide creation failed"]
                    ]
                ], 400);  
            }
        }catch(\Exception $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->validator->errors(),
            ], 422);
        }
       
    }
    function UpdateTourGuide(Request $request) {
        try {
            // $validatedData = $request->validate([
            //     'tour_id' => 'required',
            //     'email' => 'required|string|max:255|min:10|regex:/^\S*$/|unique:tour_guides,email',
            //     'name' => 'required|string|max:255|min:5|regex:/^[\p{L}\s]+$/u',
            //     'phone' => 'required|min:10|unique:tour_guides,phone',
            //     'experience' => 'required|min:0|numeric',
            // ], [
            //     'email.required' => 'Account name is required.',
            //     'email.max' => 'Account name must be between 10 and 255 characters.',
            //     'email.min' => 'Account name must be between 10 and 255 characters.',
            //     'email.regex' => 'Email cannot contain spaces.',
            //     'email.unique' => 'This email already exists in the system.',
            //     'name.required' => 'Name is required.',
            //     'name.max' => 'Account name must be between 5 and 255 characters.',
            //     'name.min' => 'Account name must be between 5 and 255 characters.',
            //     'name.regex' => 'Name can only contain letters and spaces, and cannot include special characters.',
            //     'phone.required' => 'Phone is required.',
            //     'experience.required' => 'Experience is required.',
            //     'experience.numeric' => 'Experience must be a number.',
            //     'experience.min' => 'Experience must be at least 0.'
            // ]);
            //
            // if (!$this->isValidEmail($validatedData['email'])) {
            //     return response()->json([
            //         'message' => 'Validation failed',
            //         'errors' => [
            //             'email' => ['Email must be formatted as follows: abc@gmail.com.']
            //         ],
            //     ], 422);
            // };
            // if (!$this->isValidPhoneNumber($validatedData['phone'])) {
            //         return response()->json([
            //             'message' => 'Validation failed',
            //             'errors' => [
            //                 'phone' => ['Phone number must be formatted as follows: 0123456789 or +84123456789.'],
            //             ],
            //         ], 422);
            //     }
            //     $phone = $validatedData['phone'];
            //     $phoneNumber = '0' . substr($phone, 3);
            //     $phoneNumber2 = '+84' . substr($phone, 1);
            //     $tourGuides = TourGuide::all();

            //     foreach ($tourGuides as $tourGuide) {
            //         if ($tourGuide->phone == $phoneNumber || $tourGuide->phone == $phoneNumber2) {
            //             return response()->json([
            //                 'message' => 'Validation failed',
            //                 'errors' => [
            //                     'phone' => ['Phone number already exists.']
            //                 ],
            //             ], 422);
            //         }    
            // }
  
            // $key = 'dat123';
            // $encodedTourId = $validatedData['tour_id'];
            // $tourId = $this->decryptId($encodedTourId, $key);
            // if (!$tourId) {
            //     return response()->json([
            //         "error" => "Invalid tour ID.",
            //     ], 404);
            // }
            // $tourGuide = TourGuide::find($tourId);
          
            // if (!$tourGuide) {
            //     return response()->json([
            //         "message" => "Tour guide not found.",
            //     ], 404);
            // } else {
            //     $tourGuide->name = $validatedData['name'];
            //     $tourGuide->email = $validatedData['email'];
            //     $tourGuide->phone = $validatedData['phone'];
            //     $tourGuide->experience = $validatedData['experience'];
            //     $tourGuide->save();
            //     return response()->json([
            //         "message" => "Update successful tour guide",
            //         "data" => $tourGuide
            //     ], 200); 
            // }
            $key = 'dat123';
            $tourGuide = new TourGuide();
            $tourData = $tourGuide->updateTourGuide($request->all(), $key);
            if ($tourData) {
                return response()->json([
                    "message" => "Update successful tour guide",
                    "data" => $tourData
                ], 201);  
            } else {
                return response()->json([
                    "errors" => [
                        'errors' => ["Tour guide creation failed"]
                    ]
                ], 400);  
            }
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    function deleteTourGuide(Request $request) {
        $key = 'dat123';
        try {
            // $validatedData = $request->validate([
            //     'tour_id' => 'required',
            // ], [
            //     'tour_id.required' => 'Tour ID is required.', 
            // ]);
            // $encodedTourId = $validatedData['tour_id'];
            // $tourId = $this->decryptId($encodedTourId, $key);
            // if (!$tourId) {
            //     return response()->json([
            //         "error" => "Invalid tour ID.",
            //     ], 404);
            // }
    
            // $tourDestroy = tourGuide::destroy($tourId);
            $tourGuide = new TourGuide();
            $tourDestroy = $tourGuide->deleteTourGuide($request->all(), $key);
            if ($tourDestroy) {
                return response()->json([
                    "message" => "delete tour successful",
                ], 200);
            } else {
                return response()->json([
                    'errors' => [
                        'error' => ['delete tour fail.']
                    ],
                ], 404);
            }      
        } 
        catch (ValidationException $e) {
                return response()->json([
                    "message" => "An error occurred",
                    "error" => $e->getMessage() 
                ], 422);
        }catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        } 
    }
    function getTourGuideID(Request $request) {
        $key = 'dat123';
        try {
            // $validatedData = $request->validate([
            //     'tour_id' => 'required',
            // ], [
            //     'tour_id.required' => 'Tour ID is required.', 
            // ]);
            // $encodedTourId = $validatedData['tour_id'];
            // $tourId = $this->decryptId($encodedTourId, $key);

            // if (!$tourId) {
            //     return response()->json([
            //         "error" => "Invalid tour ID.",
            //     ], 404);
            // }
    
            // $tourData = tourGuide::find($tourId);
            $tour = new TourGuide();
            $tourData = $tour->getTourGuideID($request->all(),$key); 
            if ($tourData) {
                return response()->json([
                    "message" => "get tour successful",
                    "data" => $tourData,
                ], 200);
            } else {
                return response()->json([
                    'errors' => [
                        'error' => ['Phone number already exists.']
                    ],
                ], 404);
            }      
        } 
        catch (ValidationException $e) {
                return response()->json([
                    "message" => "An error occurred",
                    "error" => $e->getMessage() 
                ], 422);
        }catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        } 
    }
  

}
