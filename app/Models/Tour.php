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
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }


}
