<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class UserDetails extends Model
{
    use HasFactory;
    protected $table = 'user_details';

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'email',
        'profile_picture',
        'gender',
        'dob',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public static function createUserDetail($date,$data,$idUser)
    {
        return self::create([
            'user_id' => $idUser,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'email' => $data['email'] ?? null,
            'profile_picture' => $data['profile_picture'] ?? null,
            'gender' => $data['gender'],
            'dob' => $date,
        ]);
    }
}