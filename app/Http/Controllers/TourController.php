<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tour;
use App\Models\User;
use App\Models\TourGuide;
use App\Models\Customer;
use App\Models\Contact;
use App\Models\Images;
use App\Models\Booking;
use App\Models\Favorite;
use App\Models\Schedule;
use App\Models\HashSecret;
use Storage;
use File;
use Illuminate\Support\Facades\Log;


class TourController extends Controller
{
    /**
     * Get tours
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Lấy số lượng tour mỗi trang từ request
            $perPage = $request->input('per_page', 10);

            // Lấy tham số sắp xếp từ query string (mặc định sắp xếp theo giá)
            $sortBy = $request->query('sort', 'price');

            // Khởi tạo truy vấn
            $tours = Tour::with('images', 'schedules');

            // Sắp xếp theo tiêu chí
            switch ($sortBy) {
                case 'price':
                    $tours->orderBy('price', 'desc'); // Sắp xếp theo giá giảm dần
                    break;
                case 'latest':
                    $tours->orderBy('created_at', 'desc'); // Sắp xếp theo thời gian tạo mới nhất
                    break;
                default:
                    return response()->json(['message' => 'Invalid sort parameter.'], 400);
            }

            // Phân trang kết quả
            $tours = $tours->paginate($perPage);

            // Tạo mảng tour tùy chỉnh để trả về
            $toursArray = $tours->getCollection()->map(function ($tour) {
                return [
                    'id' => HashSecret::encrypt($tour->id), // Mã hóa ID tour
                    'name' => $tour->name,
                    'description' => $tour->description,
                    'duration' => $tour->duration,
                    'price' => $tour->price,
                    'start_date' => $tour->start_date,
                    'end_date' => $tour->end_date,
                    'location' => $tour->location,
                    'availability' => $tour->availability,
                    'images' => $tour->images,
                    'schedules' => $tour->schedules,
                ];
            });

            return response()->json([
                'tours' => $toursArray,
                'links' => [
                    'next' => $tours->nextPageUrl(),
                    'prev' => $tours->previousPageUrl(),
                ],
                'meta' => [
                    'current_page' => $tours->currentPage(),
                    'last_page' => $tours->lastPage(),
                    'per_page' => $tours->perPage(),
                    'total' => $tours->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }


     /**
      * Create tour
      * @param \Illuminate\Http\Request $request
      * @return mixed|\Illuminate\Http\JsonResponse
      */
     public function store(Request $request)
     {
        try {
            // dd($request);
            $schedule = null;
            //Make vaildate for variable
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'duration' => 'required|integer',
                'price' => 'required|integer',
                // 'start_date' => 'required|date',
                // 'end_date' => 'required|date|after:start_date',
                'location' => 'required|string',
                'images.*' => 'required|file',
                'schedules' => 'nullable',
            ]);

            $tour = Tour::create($validatedData);

            //Get array schedules
            if ($request->has('schedules')) {
                $schedules = json_decode($validatedData['schedules'], true);
                foreach($schedules as $item) {
                    $schedule = Schedule::create([
                        'name' => $item['name_schedule'],
                        'time' => $item['time_schedule'],
                        'tour_id' => $tour->id,
                    ]);
                }
            }

            // Handle file uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = time() . '_' . $image->getClientOriginalName();
                    Storage::disk('public')->put($path, File::get($image));
                    $image = Images::create([
                        'tour_id' => $tour->id,
                        'image_url' => $path,
                        'alt_text' => $request->input('alt_text', 'Default alt text'),
                    ]);
                    // http://127.0.0.1:8000/images/7B9dDErH16ywJWIhieXV9sRYitUb0dC5qNgJ0jCo.png
                }

            }

            return response()->json([
                'message' => "Tour successfully created",
                'tour' => $tour,
                'image' => $image,
                'schedule' => $schedule,
            ], 200);



        } catch (\Exception $e) {
            // Ghi lỗi vào file log
            Log::error('Error creating tour: ' . $e->getMessage());

            return response()->json([
                'message' => "something really wrong",
                'error' => $e->getMessage()
            ], 500);
        }
     }


    /**
     * Update tour
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $hashId)
    {
        try {
            $schedules = null;
            $id = HashSecret::decrypt($hashId); // Decrypt the hash ID
            $tour = Tour::with('images', 'schedules')->find($id);
            // Check if tour exists
            if (!$tour) {
                return response()->json([
                    'message' => "Tour not found",
                ], 404);
            }

            // Validate data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'duration' => 'required|integer',
                'price' => 'required|integer',
                // 'start_date' => 'required|date',
                // 'end_date' => 'required|date|after:start_date',
                'location' => 'required|string',
                'images.*' => 'nullable',
                'schedules' => 'nullable',
            ]);

            // Update tour
            $tour->update($validatedData);

            // Update schedules if provided
            if ($request->has('schedules')) {
                // Delete existing schedules
                $tour->schedules()->delete();

                // Get array of new schedules
                $schedules = json_decode($validatedData['schedules'], true);
                foreach ($schedules as $item) {
                    Schedule::create([
                        'name' => $item['name_schedule'],
                        'time' => $item['time_schedule'],
                        'tour_id' => $tour->id,
                    ]);
                }
            }
            $uploadedImages = [];
            // Handle file uploads
            if ($request->hasFile('images')) {
                //Delete tour exitting
                $images = $tour->images;
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
                    Images::create([
                        'tour_id' => $tour->id,
                        'image_url' => $path,
                        'alt_text' => $request->input('alt_text', 'Default alt text'),
                    ]);
                }
            }

            return response()->json([
                'message' => "Tour successfully updated",
                'tour' => $tour,
                'image' => $uploadedImages,
                'schedules' => $schedules,
                'id' => HashSecret::encrypt($tour->id), // Updated to encrypt the tour ID
            ], 200);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error updating tour: ' . $e->getMessage());

            return response()->json([
                'message' => "Something went wrong",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Destroy tour
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($hashId)
    {
        try {
            $id = HashSecret::decrypt($hashId); // Decrypt the hash ID
            $tour = Tour::find($id);
            // Check if tour exists
            if (!$tour) {
                return response()->json([
                    "message" => "Tour not found",
                ], 404);
            }

            // Delete tour
            $tour->delete();

            return response()->json([
                "message" => "Destroy successfully",
            ], 200);
        } catch (\Exception $e) {
            // Write bug on file log
            Log::error("Error destroy tour". $e->getMessage());
            return response()->json([
                "message" => "Something Went Wrong",
                "error" =>  $e->getMessage()
            ], 500);
        }
    }

    /**
     * Destroy many tours
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroyTours(Request $request)
    {
        try {
            $tours = Tour::whereIn('id', $request->input('ids'))->get(); // Assuming 'ids' is the key in the request
            // Check if tour not exists
            if ($tours->isEmpty()) {
                return response()->json([
                    "message" => "Tour not found",
                ], 404);
            }

            // Delete item of array tours
            foreach ($tours as $tour) {
                $tour->delete();
            }
            return response()->json([
                "message" => "Destroy Tour successfully",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Something Went Wrong",
                "error" => $e
            ], 500);
        }
    }

    public function show($hashId)
    {
        try {
            //Decrypt id
            $id = HashSecret::decrypt($hashId);
            //
            $tour = Tour::with('images', 'schedules')->find($id);
            //Check if tour not exits
            if (!$tour) {
                return response()->json([
                    "message" => "Tour not found sdfsdfsdfdư" ,
                ], 404);
            }

            //Return when find the tour
            return response()->json([
                "tour" => $tour,
                "id" => HashSecret::encrypt($tour->id) // Updated to encrypt the tour ID
            ], 200);
        }catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Xử lý trường hợp giải mã không thành công
            return response()->json([
                "message" => "Tour Not Found",
                "error" => $e->getMessage(),
            ], 400); // 400 Bad Request
        } catch (\Exception $e) {
            return response()->json([
                "message"=> "Something Went Wrong",
                "error"=> $e->getMessage()
            ], 500);
        }
    }

    public function displayNewstTour(Request $request) {
        try {
            
            $user = $request->user_id;
            $newstTour = Tour::getLatestTours();
            $encryptedTours = $newstTour->map(function($tour) use ($user) {  
             $isFavorite = Favorite::where('user_id', $user)
                                      ->where('tour_id', $tour->id)
                                      ->exists();
    
                return [
                    'id' => HashSecret::encrypt($tour->id),
                    'name' => $tour->name,
                    'description' => $tour->description,
                    'duration' => $tour->duration,
                    'price' => $tour->price,
                    'start_date' => $tour->start_date,
                    'end_date' => $tour->end_date,
                    'location' => $tour->location,
                    'availability' => $tour->availability,
                    'create_at' => $tour->create_at,
                    'update_at' => $tour->update_at,
                    'images' => $tour->images,
                    'is_favorite' => $isFavorite, 
                ];
            });
    
            if ($newstTour->isEmpty()) {
                return response()->json([
                    "message" => "Tour not found",
                ], 404);
            } else {
                return response()->json([
                    "message" => "Get tour successfully",
                    "data" => $encryptedTours,
                ], 200);
            }

        } catch (QueryException $e) {
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

    public function isValidEmail($username)
    {
        return filter_var($username, FILTER_VALIDATE_EMAIL);
    }
    //dat tours
    public function bookTour(Request $request) {
        $key = 'dat123';
        try {
            $validatedData = $request->validate([
                'tour_id' => 'required',
                'nameContact' => 'required|string|max:255|min:5|',
              'emailContact' => 'required|string|min:10|max:255|regex:/^\S*$/|unique:contacts,email',
                'nameCustomer' => 'required|string|max:255|min:5|',
              'emailCustomer' => 'required|string|min:10|max:255|regex:/^\S*$/|unique:customers,email',
                // 'emailCustomer' => 'required|string|min:10|max:255|regex:/^\S*$/',
                'totalPrice' => 'required|numeric|min:0',
                'type_customer' => 'required|in:self,other',
                'number_of_adult' => 'required|numeric|min:0',
                'number_of_childrent' => 'required|numeric|min:0',
            ], [
                'tour_id' => 'tour id is required',
                'nameContact.required' => 'name contact is required',
                'nameContact.max' => 'name contact must be between 5 and 255 characters.',
                'nameContact.min' => 'name contact must be between 5 and 255 characters.',
                'emailContact.required' => 'email contact is required.',
                'emailContact.min' => 'email contact must be between 10 and 255 characters.',
                'emailContact.max' => 'email contact must be between 10 and 255 characters.',
                'emailContact.regex' => 'email contact  cannot contain spaces.',
                'emailContact.unique' => 'email already exists in the system',
                'nameCustomer.required' => 'name customer is required.',
                'nameCustomer.max' => 'name customer must be between 5 and 255 characters.',
                'emailCustomer.required' => 'email contact is required.',
                'emailCustomer.min' => 'email contact must be between 10 and 255 characters.',
                'emailCustomer.max' => 'email contact must be between 10 and 255 characters.',
                'emailCustomer.regex' => 'email contact cannot contain spaces.',
                'emailCustomer.unique' => 'email already exists in the system',
                'totalPrice.required' => 'total price is required',
                'totalPrice.numeric' => 'total price must be a number',
                'totalPrice.numeric' => 'must not be less than 0',
                'type_customer.required' => 'type is required',
                'type_customer.in' => 'invalid type',
                'number_of_adult.required' => 'number of adult is required',
                'number_of_adult.numeric' => 'number of adult must be a number',
                'number_of_adult.min' => 'number of adult must not be less than 0',
                'number_of_childrent.required' => 'number of children is required',
                'number_of_childrent.numeric' => 'number of children must be a number',
                'number_of_childrent.min' => 'number of children must not be less than 0',
            ]);

            $encodedTourId = $validatedData['tour_id'];
            $tourId = HashSecret::decrypt($encodedTourId);
            if (!$tourId) {
                return response()->json([
                    "error" => ["Invalid tour ID."],
                ], 404);
            }
            if(!$this->isValidEmail($validatedData['emailContact'])) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'emailContact' => ['Email must be formatted as follows: abc@gmail.com.']
                    ],
                ], 422);
            } else if(!$this->isValidEmail($validatedData['emailCustomer'])) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'emailCustomer' => ['Email must be formatted as follows: abc@gmail.com.']
                    ],
                ], 422);
            }
            // else if (Contact::where('email', $validatedData['emailContact'])->exists()) {
            //     return response()->json([
            //         'message' => 'Validation failed',
            //         'errors' => [
            //             'emailContact' => ['Email already exists']
            //         ],
            //     ], 422);
            // }
            // else if (Contact::where('email', $validatedData['emailCustomer'])->exists()) {
            //     return response()->json([
            //         'message' => 'Validation failed',
            //         'errors' => [
            //             'emailCustomer' => ['Email already exists']
            //         ],
            //     ], 422);
            // }
            // else if(User::find($validatedData['emailCustomer']) || TourGuide::find($validatedData['emailCustomer'])) {
            //     return response()->json([
            //         'message' => 'Validation failed',
            //         'errors' => [
            //             'emailCustomer' => ['Email already exists']
            //         ],
            //     ], 422);
            // }
            // else if(User::find($validatedData['emailContact']) || TourGuide::find($validatedData['emailContact'])) {
            //     return response()->json([
            //         'message' => 'Validation failed',
            //         'errors' => [
            //             'emailContact' => ['Email already exists']
            //         ],
            //     ], 422);
            // }




            $contact = Contact::create([
               'name' => $validatedData['nameContact'],
               'email' => $validatedData['emailContact'],
            ]);
            if(!$contact) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'error' => ['An error occurred when creating contact information']
                    ],
                ], 422);
            }
            if($validatedData['type_customer'] == 'other') {
                $customer = Customer::create([
                    'contact_id' => $contact->id,
                    'name' => $validatedData['nameCustomer'],
                    'email' => $validatedData['emailCustomer'],
                    'type_customer' => $validatedData['type_customer'],
                ]);
            } else if($validatedData['type_customer'] == 'self') {
                $customer = Customer::create([
                    'contact_id' => $contact->id,
                    'type_customer' => $validatedData['type_customer'],
                ]);
            }
            if(!$customer) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'error' => ['An error occurred when creating customer information']
                    ],
                ], 422);
            }
            $number_of_people = $validatedData['number_of_adult'] + $validatedData['number_of_childrent'];
            $booking = Booking::create([
                'tour_id' => $tourId,
                'customer_id' => $customer->id,
                'number_of_adult' =>  $validatedData['number_of_adult'],
                'number_of_childrent' =>  $validatedData['number_of_childrent'],
                'number_of_people' =>  $number_of_people,
                'total_price' =>  $validatedData['totalPrice'],
            ]);
            if(!$booking) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'error' => ['An error occurred when creating booking information']
                    ],
                ], 422);
            }
            return response()->json([
                'message' => 'Booking the tour successfully',
                'booking' => $booking,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'submit failed',
                'errors' => $e->validator->errors(),
            ], 422);
        }
    }


    //xem chi tiet tour
    public function TourDetail(Request $request) {
        try {

            $validatedData = $request->validate([
                'tour_id' => 'required',
            ], [
                'tour_id.required' => 'Tour ID is required.',
            ]);
            $encodedTourId = $validatedData['tour_id'];
            $tourId = HashSecret::decrypt($encodedTourId);
            if (!$tourId) {
                return response()->json([
                    "error" => "Invalid tour ID.",
                ], 404);
            }


            $tourDetail = Tour::getTourDetailWithImages($tourId);

            if ($tourDetail) {
                return response()->json([
                    "message" => "Get tour successful",
                    'data' => $tourDetail,
                ], 200);
            } else {
                return response()->json([
                    "error" => "Tour not found.",
                ], 404);
            }
        }catch (\Exception $e) {
            return response()->json([
                "message" => "An unexpected error occurred",
                "error" => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Look for tour with location
     * @param mixed $query
     * @param mixed $location
     * @return mixed
     */
    public function findByLocation($location)
    {
        try {
            $tours = Tour::findByLocation($location)->with('images')->get();
            // Check if results are found
            if ($tours->isEmpty()) {
                return response()->json([
                    'message' => 'Tour Not Foud.'
                ], 404);
            }

            $toursArray = $tours->map(function ($tour) {
                return [
                    'id' => HashSecret::encrypt($tour->id), // Mã hóa ID tour
                    'name' => $tour->name,
                    'description' => $tour->description,
                    'duration' => $tour->duration,
                    'price' => $tour->price,
                    'start_date' => $tour->start_date,
                    'end_date' => $tour->end_date,
                    'location' => $tour->location,
                    'availability' => $tour->availability,
                    'images' => $tour->images,
                    'schedules' => $tour->schedules,
                ];
            });


            // Return the results
            return response()->json([
                'tours' => $toursArray,
            ]
            ,200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display tour with category
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function findByCategory($category)
    {
        // Validate the incoming request
        try {
            // Use the scope method to find tours by category
            $results = Tour::findByCategory($category)->get();

            // Check if results are found
            if ($results->isEmpty()) {
                return response()->json([
                    'message' => 'No tours found for this category.'
                ], 404);
            }

            // Return the results
            return response()->json($results, 200);

        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json([
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Method get count tour upload
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function countTours()
    {
        try {
            // Lấy số lượng tour đã đăng
            $count = Tour::count();
            // Trả về số lượng tour
            return response()->json([
                'count' => $count
            ], 200);

        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo
            return response()->json([
                'message' => 'An error occurred while counting tours.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Method update stutus
     * @param mixed $tatus
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateStatus($tatus, $id)
    {

        try {
            //Check tour not foud
            $tour = Tour::findOrFail($id);
            // Check if results are found
            if ($tour->isEmpty()) {
                return response()->json([
                    'message' => 'No tours found.'
                ], 404);
            }
            $tour->availability = $tatus;
            $tour->save();

            return response()->json([
                'message' => 'Tour status updated successfully.',
                'tour' => $tour
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Tour not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating tour status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sortTours(Request $request)
    {
        $sortBy = $request->query('sort', 'price');
        try {
            $tours = Tour::query();
            // Sắp xếp theo tiêu chí
            switch ($sortBy) {
                case 'price':
                    $tours->orderBy('price', 'desc');
                    break;
                case 'latest':
                    $tours->orderBy('created_at', 'desc');
                    break;
                default:
                    return response()->json(['message' => 'Invalid sort parameter.'], 400);
            }

            $results = $tours->get();
            return response()->json($results, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while sorting tours.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // http://your-domain.com/images/your_image_filename.jpg
    public function showImage($filename)
    {
        $path = 'D:/uploads/images/' . $filename; // Đường dẫn đến hình ảnh

        if (!File::exists($path)) {
            abort(404); // Trả về 404 nếu không tìm thấy hình ảnh
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        // return Response::make($file, 200)->header("Content-Type", $type);
    }



}