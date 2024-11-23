<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tour;

class NotificationTour extends Model
{
    use HasFactory;
    protected $table = 'notification_tours';
    protected $fillable = ['tour_id', 'user_id', 'read'];
    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
