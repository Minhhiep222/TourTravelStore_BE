<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = "payments";

    protected $fillable = [
        "tour_id",
        "number_of_tickers",
        "total_price",
        "user_id",
        "payment_method",
        "status",
        "notes",
        "transaction_id",
    ];
     // Quan hệ với bảng tours
     public function tour()
     {
         return $this->belongsTo(Tour::class);
     }
 
     // Quan hệ với bảng users
     public function user()
     {
         return $this->belongsTo(User::class);
     }
}