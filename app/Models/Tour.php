<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Images;
use App\Models\Schedule;
use App\Models\User;
use App\Models\HashSecret;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Storage;
use File;

class Tour extends Model
{
    use HasFactory;

    /**
     * Summary of table
     * @var string
     */
    protected $table = "tours";

    /**
     * Summary of fillable
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'duration',
        'price',
        'start_date',
        'end_date',
        'location',
        'availability',
        'user_id',
        'status'
    ];

    /**
     * Summary of images
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(Images::class, 'tour_id', 'id');
    }

    /**
     * Summary of reviews
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews() {
        return $this->hasMany(Review::class, 'tour_id', 'id');
    }

    /**
     * Summary of user
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Summary of schedules
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'tour_id', 'id');
    }

    /**
     * Summary of getTourDetailWithImages
     * @param mixed $tourId
     * @return Model|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|null
     */
    public static function getTourDetailWithImages($tourId)
    {
        return self::with('images', 'user')->find($tourId);
    }

    /**
     * Summary of getLatestTours
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getLatestTours()
    {
        return self::with('images','reviews')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Extension method with FindByLocation
     * @param mixed $query
     * @param mixed $location
     * @return mixed
     */
    public function scopeFindByLocation($query, $location)
    {
        return $query->where('location', 'LIKE', '%' . $location . '%');
    }
    /**
     * Extension method with FindByCategory
     * @param mixed $query
     * @param mixed $category
     * @return mixed
     */
    public function scopeFindByCategory($query, $category)
    {
        return $query->where('category', 'LIKE', '%' . $category . '%');
    }

    /**
     * Get Tour By User Id
     * @param mixed $userId
     * @param mixed $perPage
     * @param mixed $sortBy
     * @return array
     */
    public function getToursByUser($userId, $perPage = 10, $sortBy = 'price')
    {
        //Get tours with image schedules and review
        $tours = Tour::with('images', 'schedules', 'reviews')
            ->where("user_id", $userId)
            ->when($sortBy === 'price', fn($q) => $q->orderBy('price', 'desc'))
            ->when($sortBy === 'latest', fn($q) => $q->orderBy('created_at', 'desc'))
            ->paginate($perPage);

        return $this->transformTourResponse($tours);
    }

    public function getToursByApp($perPage = 10, $sortBy = 'price')
    {
        //Get tours with image schedules and review
        $tours = Tour::with('images', 'schedules', 'reviews')
            ->where("availability", "1")
            ->when($sortBy === 'price', fn($q) => $q->orderBy('price', 'desc'))
            ->when($sortBy === 'latest', fn($q) => $q->orderBy('created_at', 'desc'))
            ->paginate($perPage);

        return $this->transformTourResponse($tours);
    }

    /**
     * Get array data tours
     * @param mixed $tours
     * @return array
     */
    private function transformTourResponse($tours)
    {
        $toursArray = $tours->getCollection()->map(function ($tour) {
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
                'images' => $tour->images,
                'schedules' => $tour->schedules,
                'reviews' => $tour->reviews,
                'status' => $tour->status,
                'avgReview' => $this->totalAverageRating($tour->reviews)
            ];
        });

        return [
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
        ];
    }

    /**
     * Summary of createTour with transaction
     * @param array $data
     * @param mixed $images
     * @return mixed
     */
    public function createTour(array $data, $images = null)
    {
        //Toàn vèn dữ liệu transaction
        return DB::transaction(function () use ($data, $images) {
            // Tạo tour

            $tour = Tour::create($this->prepareTourData($data));

            // Xử lý schedule
            $schedule = $this->handleSchedules($data, $tour);

            // Xử lý hình ảnh
            $processedImages = $this->handleImages($images, $tour);

            return [
                'tour' => $tour,
                'schedule' => $schedule,
                'image' => $processedImages
            ];
        });
    }

    /**
     * Summary of totalAverageRating
     * @param mixed $reviews
     * @return float|int|null
     */
    public static function totalAverageRating($reviews) {
        return round(collect($reviews)->avg('rating'), 1);
    }

    /**
     * Summary of prepareTourData return array data
     * @param array $data
     * @return array
     */
    private function prepareTourData(array $data)
    {
        // dd($data['status']);
        return [
            'name' => $data['name'],
            'description' => $data['description'],
            'duration' => $data['duration'],
            'price' => $data['price'],
            'location' => $data['location'],
            'status' => $data['status'],
            'user_id' => $data['user_id'] ?? auth()->id()
        ];
    }

    /**
     * Summary of handleSchedules handle create Schedules if it not null
     * @param array $data
     * @param \App\Models\Tour $tour
     * @return \Illuminate\Support\Collection|null
     */
    private function handleSchedules(array $data, Tour $tour)
    {
        //Return null when schedules not contraint data
        if (!isset($data['schedules'])) return null;

        //Convert data json to array
        $schedules = json_decode($data['schedules'], true);
        return collect($schedules)->map(function ($item) use ($tour) {
            return Schedule::create([
                'name' => $item['name_schedule'],
                'time' => $item['time_schedule'],
                'tour_id' => $tour->id,
            ]);
        });
    }

    /**
     * Summary of handleImages handle create Images if it not null
     * @param mixed $images
     * @param \App\Models\Tour $tour
     * @return \Illuminate\Support\Collection|null
     */
    private function handleImages($images, Tour $tour)
    {
        //Return null when image not contraint data
        if (!$images) return null;

        //Use collection and callback
        return collect($images)->map(function ($image) use ($tour) {
            $path = time() . '_' . $image->getClientOriginalName();
            Storage::disk('public')->put($path, File::get($image));
            return Images::create([
                'tour_id' => $tour->id,
                'image_url' => $path,
                'alt_text' => request()->input('alt_text', 'Default alt text'),
            ]);
        });
    }

    /**
     * Summary of updateTour with array data and id
     * @param mixed $hashId
     * @param array $data
     * @param mixed $newImages
     * @return mixed
     */
    public function updateTour($hashId, array $data, $newImages = null)
    {
        return DB::transaction(function () use ($hashId, $data, $newImages) {
            // Giải mã ID
            // dd($data['status']);
            $id = HashSecret::decrypt($hashId);
            $tour = Tour::with('images', 'schedules')->findOrFail($id);

            // Cập nhật thông tin tour
            $tour->update($this->prepareTourData($data));
            // Xử lý schedules
            $schedules = $this->handleUpdateSchedules($data, $tour);

            // Xử lý images
            $images = $this->handleUpdateImages($newImages, $tour);

            return [
                'tour' => $tour,
                'images' => $images,
                'schedules' => $schedules,
                'encrypted_id' => HashSecret::encrypt($tour->id)
            ];
        });
    }

    /**
     * Summary of handleUpdateSchedules handle update Schedules
     * @param array $data
     * @param \App\Models\Tour $tour
     * @return \Illuminate\Support\Collection|null
     */
    private function handleUpdateSchedules(array $data, Tour $tour)
    {
        if (!isset($data['schedules'])) return null;

        // Xóa schedules cũ
        $tour->schedules()->delete();

        // Tạo schedules mới
        return $this->handleSchedules($data,$tour);

    }

    /**
     * Summary of handleUpdateImages with tour and new images
     * @param mixed $newImages
     * @param \App\Models\Tour $tour
     * @return \Illuminate\Support\Collection|null
     */
    private function handleUpdateImages($newImages, Tour $tour)
    {
        if (!$newImages) return null;

        // Xóa images cũ
        $oldImages = $tour->images;
        foreach ($oldImages as $image) {
            $filePath = public_path('/images/' . $image->image_url);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $image->delete();
        }

        // Tạo images mới
        return $this->handleImages($newImages,  $tour);
    }


    /**
     * Summary of deleteTour
     * @param mixed $hashId
     * @return mixed
     */
    public function deleteTour($hashId)
    {
        return DB::transaction(function () use ($hashId) {
            $id = HashSecret::decrypt($hashId);

            $tour = Tour::with(['images', 'schedules'])->findOrFail($id);

            // Xóa files ảnh trước
            $this->deleteAssociatedImages($tour);

            // Soft delete hoặc force delete tùy yêu cầu
            $tour->delete();

            return true;
        });
    }

    /**
     * Summary of deleteAssociatedImages with image exit
     * @param \App\Models\Tour $tour
     * @return void
     */
    private function deleteAssociatedImages(Tour $tour)
    {
        foreach ($tour->images as $image) {
            $this->deleteImageFile($image);
            $image->delete();
        }
    }

    /**
     * Summary of deleteImageFile delete file image in storage
     * @param \App\Models\Images $image
     * @return void
     */
    private function deleteImageFile(Images $image)
    {
        $filePath = public_path('/images/' . $image->image_url);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

}