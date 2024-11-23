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
use App\Models\NotificationTour;
use App\Models\HashSecret;
use App\Events\TourCreated;
use App\Events\Notify;

use Storage;
use File;
use Illuminate\Support\Facades\Log;


class TourController extends Controller
{

    protected $tourService;

    public function __construct(Tour $tourService)
    {
        $this->tourService = $tourService;
    }

    /**
     * Get tours
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
     {
         try {
             $result = $this->tourService->getToursByUser(
                 $request->input('user_id', 10),
                 $request->input('per_page', 10),
                 $request->query('sort', 'price')
             );

             return response()->json($result, 200);
         } catch (\Exception $e) {
             return response()->json([
                 'error' => $e->getMessage(),
             ], 500);
         }
    }

    public function product(Request $request)
     {
         try {
            $result = $this->tourService->getToursByApp(
                $request->input('per_page', 10),
                $request->query('sort', 'price')
            );

             return response()->json($result, 200);
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
              $validatedData = $request->validate([
                  'name' => 'required|string|max:255',
                  'description' => 'required|string',
                  'duration' => 'required|integer',
                  'price' => 'required|integer',
                  'location' => 'required|string',
                  'images.*' => 'required|file',
                  'schedules' => 'nullable',
                  'user_id' => 'nullable',
              ]);

              $result = $this->tourService->createTour($validatedData, $request->file('images'));

              $tour = Tour::with('images')->find(1);
              // $tour->tour_id = HashSecret::encrypt($tour->tour_id);

              broadcast(new Notify($tour));

              return response()->json([
                  'message' => "Tour successfully created",
                  'tour' => $result['tour'],
                  'image' => $result['image'],
                  'schedule' => $result['schedule'],
              ], 200);

          } catch (\Exception $e) {
              Log::error('Error creating tour: ' . $e->getMessage());

              return response()->json([
                  'message' => "Something went wrong",
                  'error' => $e->getMessage()
              ], 500);
          }
      }


    //  public function store(Request $request)


    /**
     * Update tour
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */

    public function update(Request $request, $hashId)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'duration' => 'required|integer',
                'price' => 'required|integer',
                'location' => 'required|string',
                'images.*' => 'nullable',
                'schedules' => 'nullable',
                'status' => 'required',
                'user_id' => 'nullable',
            ]);


            $result = $this->tourService->updateTour($hashId, $validatedData, $request->file('images'));

            return response()->json([
                'message' => "Tour successfully updated",
                'tour' => $result['tour'],
                'image' => $result['images'],
                'schedules' => $result['schedules'],
                'id' => $result['encrypted_id'],
            ], 200);

        } catch (\Exception $e) {
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
            $result = $this->tourService->deleteTour($hashId);

            return response()->json([
                "message" => "Tour deleted successfully"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => "Tour not found"
            ], 404);

        } catch (\Exception $e) {
            Log::error("Error deleting tour: " . $e->getMessage());

            return response()->json([
                "message" => "Something went wrong",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summary of show
     * @param mixed $hashId
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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

    /**
     * Summary of displayNewstTour
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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
                    'avgReview' => Tour::totalAverageRating( $tour->reviews),
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

        } catch (\Exception $e) {
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

    /**
     * Summary of isValidEmail
     * @param mixed $username
     * @return mixed
     */
    public function isValidEmail($username)
    {
        return filter_var($username, FILTER_VALIDATE_EMAIL);
    }
    //dat tours
    public function bookTour(Request $request) {
        try {
            $validatedData = $request->validate([
                'tour_id' => 'required',
                'nameContact' => 'required|string|max:255|min:5|',
                'emailContact' => 'required|string|min:10|max:255|regex:/^\S*$/|unique:contacts,email',
                'nameCustomer' => 'required|string|max:255|min:5|',
                'emailCustomer' => 'required|string|min:10|max:255|regex:/^\S*$/|unique:customers,email',
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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'submit failed',
            ], 422);
        }
    }

    /**
     * Summary of TourDetail
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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
                    // 'user' => $tourDetail->user
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
    public function countTours(Request $request)
    {
        try {
            $user_id = (int) $request->query('user_id', 10);
            // Lấy số lượng tour đã đăng
            $count = Tour::where("user_id", $user_id)->count();
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
    public function updateStatus(Request $request, $id)
    {
        try {
            $id = HashSecret::decrypt($id);
            // Kiểm tra xem tour có tồn tại không
            $tour = Tour::findOrFail($id);

            // Cập nhật trạng thái và tính khả dụng
            $tour->status = $request->status;
            $tour->availability = $request->status === 1 ? 1 : 0;

            // Lưu thông tin vào database
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
