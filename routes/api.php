<?php



use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\TourGuideController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\GoogleMapController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatController;
use App\Events\Notify;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**Group api tours */
Route::prefix('tours')->controller(TourController::class)->group(function () {
    Route::get('/list', 'index');
    Route::get('/product', 'product');
    Route::get('/admin', 'admin');
    Route::get('{id}', 'show');
    Route::put('{id}', 'update');
    Route::delete('{id}', 'destroy');
    Route::patch('deleteAll', 'destroyTours');
    Route::post('/', 'store');
    Route::get('/search/{key}', 'findByLocation');
    Route::get('/category/{key}', 'findByCategory');
    Route::get('total/count', 'countTours');
    Route::put('/{id}/status', 'updateStatus');
    Route::get('/sort={key}', 'sortTours');
});

/**Group api payment */
Route::prefix('payments')->controller(PaymentController::class)->group(function () {
    Route::get('/list', 'index');
    Route::get('{id}', 'show');
    Route::put('{id}', 'update');
    Route::delete('{id}', 'destroy');
    Route::post('/', 'store');
    Route::put('/{id}/status', 'updateStatus');
    Route::get('/sort={key}', 'sortPayment');
    Route::post('/momo_payment', 'momo_payment');
    Route::post('/zalo_payment', 'zalo_payment');
    Route::post('/momo/ipn', 'momoIPN');
    Route::post('/zalo/ipn', 'zaloIPN');
});

/**Group api  reviews*/
Route::prefix('reviews')->controller(ReviewController::class)->group(function () {
    Route::get('/list', 'index');
    Route::get('{id}', 'show');
    Route::put('{id}', 'update');
    Route::delete('{id}', 'destroy');
    Route::post('/', 'store');
    Route::put('/{id}/status', 'updateStatus');
});

/**Group api booking */
Route::prefix('bookings')->controller(BookingController::class)->group(function () {
    Route::get('/list', 'index');
    Route::get('{id}', 'show');
    Route::put('{id}', 'update');
    Route::delete('{id}', 'destroy');
    Route::post('/', 'store');
    Route::put('/{id}/status', 'updateStatus');
    Route::get('/sort={key}', 'sortBooking');
});


/**Group api for chat real time channel */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::post('/conversations', [ChatController::class, 'startConversation']);
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'getMessages']);
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
});

/**Group route api message  */
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/messages', [ChatController::class, 'index']);
    Route::post('/messages', [ChatController::class, 'store']);
});

/**Route Broadcast when you use privatechannel  */
Broadcast::routes(['middleware' => ['auth:sanctum']]);

//
Route::post('seenAllNotification', [NotificationController::class, 'seenAllNotification']);
Route::get('checkNotifyUser', [NotificationController::class, 'checkNotifyUser']);
Route::post('turnOffNotification', [NotificationController::class, 'turnOffNotification']);
Route::post('readNotification', [NotificationController::class, 'readNotification']);
Route::get('getNotification', [NotificationController::class, 'getNotification']);
Route::get('displayEvent', [NotificationController::class, 'displayEvent']);
Route::get('getDistanceTravel', [GoogleMapController::class, 'getDistanceTravel']);
Route::get('getTimeTravel', [GoogleMapController::class, 'getTimeTravel']);
Route::get('reverseCoordinateConvertion', [GoogleMapController::class, 'reverseCoordinateConvertion']);
Route::get('coordinateConvertion', [GoogleMapController::class, 'coordinateConvertion']);
Route::get('getToursFavorite', [FavoriteController::class, 'getToursFavorite']);
Route::post('/chatbot', [ChatBotController::class, 'chatbot']);
Route::get('getAllTourGuide',[TourGuideController::class,'getAllTourGuide']);
Route::post('addTourGuide',[TourGuideController::class,'addTourGuide']);
Route::get('getTourGuideID',[TourGuideController::class,'getTourGuideID']);
Route::post('deleteTourGuide',[TourGuideController::class,'deleteTourGuide']);
Route::post('UpdateTourGuide',[TourGuideController::class,'UpdateTourGuide']);
Route::post('addTourToFavorite',[FavoriteController::class,'addTourToFavorite']);
Route::get('TourDetail',[TourController::class,'TourDetail']);
Route::post('bookTour',[TourController::class,'bookTour']);
Route::get('displayNewstTour',[TourController::class,'displayNewstTour']);
Route::post('login',[AuthController::class,'login']);
Route::middleware('auth:sanctum')->group(function(){
    Route::get('inforCurrentUser', [AuthController::class,'inforCurrentUser']);
    Route::post('createTour', [NotificationController::class, 'createTour']);
});
// Route::get('/broadcast',function(){
//     broadcast(new Notify());
// });
Route::post('RegistermoreInfomation', [AuthController::class,'RegistermoreInfomation']);
Route::post('sendCode', [AuthController::class,'sendCode']);
Route::post('registerMainInfo', [AuthController::class,'registerMainInfo']);
Route::post('mainInformation', [AuthController::class,'mainInformation']);
// Route::post('store', [TourController::class, 'store']);
//


Route::get('user', [AuthController::class, 'user']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::get('users', [UserController::class, 'index']);
Route::get('users/create', [UserController::class, 'create']);
Route::post('users', [UserController::class, 'store']);
Route::get('users/{user}/edit', [UserController::class, 'edit']);
Route::put('users/{id}', [UserController::class, 'update']);
Route::delete('users/{id}', [UserController::class, 'destroy']);

// Route::get('/search/{key}', '');
// Route::get('/category/{key}', 'findByCategory');
// Route::get('total/count', 'countTours');
// Route::put('/{id}/status', 'updateStatus');
// Route::get('/sort={key}', 'sortTours');



/**Group api payment */
Route::prefix('payments')->controller(PaymentController::class)->group(function () {
    Route::get('/list', 'index');
    Route::get('{id}', 'show');
    Route::put('{id}', 'update');
    Route::delete('{id}', 'destroy');
    Route::patch('deleteAll', 'destroyPayment');
    Route::post('/', 'store');
    // Route::get('/search/{key}', 'findByLocation');
    Route::get('/category/{key}', 'findByCategory');
    Route::get('total/count', 'countPayment');
    Route::put('/{id}/status', 'updateStatus');
    Route::get('/sort={key}', 'sortPayment');
    Route::post('/momo_payment', 'momo_payment');
    Route::post('/momo/ipn', 'momoIPN');
});
Route::delete('users/{id}', [UserController::class, 'destroy']);