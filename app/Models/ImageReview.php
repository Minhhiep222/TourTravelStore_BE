<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageReview extends Model
{
    use HasFactory;

    protected $table = "image_reviews";

    protected $fillable = [
        'review_id',
        'image_url',
        'alt_text'
    ];
}