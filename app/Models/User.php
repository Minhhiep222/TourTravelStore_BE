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
        'email_verified_at',
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
        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'username' => 'required|max:100|unique:users|not_regex:/^\s/|not_regex:/\s{2,}/',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:1,2,3',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        return self::create([
            'name' => $request['name'],
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'role' => $request['role'],
        ]);
    }

    // Cập nhật người dùng
    public static function updateUser($request, $id)
    {
        $user = self::find($id);

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại.'], 404);
        }

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'username' => 'required|max:100|regex:/[a-zA-Z]/|not_regex:/^\s/|not_regex:/\s{2,}/',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:1,2,3',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Nếu mật khẩu không được cung cấp, giữ nguyên mật khẩu cũ
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->update($request->only(['name', 'username', 'email', 'role', 'password']));

        return response()->json(['message' => 'Người dùng đã được cập nhật thành công.'], 200);
    }

    // Xóa người dùng
    public static function deleteUser($id)
    {
        $user = self::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Người dùng đã được xóa thành công.'], 200);
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
