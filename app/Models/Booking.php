<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tour;
class Booking extends Model
{
    use HasFactory;

    protected $table = "bookings";

    protected $fillable = [
        "tour_id",
        "customer_id",
        "booking_date",
        "number_of_people",
        "number_of_adult",
        "number_of_children",  
        "total_price",  
        "tour_guide_id",
    ];


    public function tour() {
        return $this->hasOne(Tour::class, 'id', 'tour_id');
    }


}