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
}