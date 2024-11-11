<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tour;
class Images extends Model
{
    use HasFactory;

    protected $table = "images";

    protected $fillable = [
        "tour_id",
        "image_url",
        "alt_text",
    ];

    // public function tour() {
    //     return $this->hasOne(Tour::class, "id" ,"tour_id");
    // }
}