<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',  // Thêm thuộc tính username
        'password',
        'email',
        'role',      // Thêm thuộc tính role
        'email_verified_at', // Thêm thuộc tính email_verified_at nếu cần
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

     public static function getAllUsers()
     {
         return self::all();
     }
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function encryptId($id, $key) {
        $method = 'AES-256-CBC';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $encryptedId = openssl_encrypt($id, $method, $key, 0, $iv);
        return base64_encode($iv . $encryptedId);
    }


    public function decryptId($encryptedId, $key) {
        $method = 'AES-256-CBC';

        $decodedUrl = urldecode($encryptedId);
        $decodedData = base64_decode($decodedUrl);
        $ivLength = openssl_cipher_iv_length($method);
        $iv = substr($decodedData, 0, $ivLength);
        $encryptedIdWithoutIv = substr($decodedData, $ivLength);

        return openssl_decrypt($encryptedIdWithoutIv, $method, $key, 0, $iv);
    }
    public static function usernameExists($username)
    {
        return self::where('username', $username)->exists();
    }
    public static function createUser($request)
    {
        return self::create([
            'username' => $request['username'],
            'password' => Hash::make($request['password']),
            'role' => $request['role'],
        ]);
    }
    public function details()
    {
        return $this->hasOne(UserDetails::class, 'user_id');
    }

}

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

}