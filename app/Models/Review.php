<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ImageReview;

class Review extends Model
{
    use HasFactory;

    protected $table = "reviews";

    protected $fillable = [
        "user_id",
        "tour_id",
        "rating",
        "comment",
        "status",
        "parent_id",
    ];


    public function image_reviews() {
        return $this->hasMany(ImageReview::class, 'review_id', 'id');
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}