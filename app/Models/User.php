<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'password',
        'email',
        'role',
        'notication',      // Thêm thuộc tính role
        'email_verified_at', // Thêm thuộc tính email_verified_at nếu cần
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Mã hóa và giải mã ID
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

    // Lấy tất cả người dùng
    public static function getAllUsers()
    {
        return self::all();
    }

    // Kiểm tra tên người dùng đã tồn tại chưa
    public static function usernameExists($username)
    {
        return self::where('username', $username)->exists();
    }

    // Thêm người dùng
    public static function createUser($request)
    {
        return self::create([
            'name' => $request['name'],
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'role' => $request['role'],
        ]);
    }
    public function details()
    {
        return $this->hasOne(UserDetails::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
