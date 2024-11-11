<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HashSecret extends Model
{
    use HasFactory;

     /**
      *  Hàm mã hóa
      * @param mixed $id
      * @return string
      */
      static function encrypt($id)
      {
        $secretKey = env('SECRET_KEY', 'tourtravelstore');
        $cipher = 'AES-256-CBC';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));

        // Mã hóa ID
        $encrypted = openssl_encrypt($id, $cipher, $secretKey, 0, $iv);

        // Kết hợp mã hóa với IV và base64 encode
        $encoded = base64_encode($encrypted . '::' . $iv);

        // Thay thế các ký tự đặc biệt trong base64 với URL-safe base64
        $encoded = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);

        return $encoded;
      }

    /**
     * Hàm giải mã
     * @param mixed $data
     * @return bool|string
     */
    static function decrypt($data)
    {
        $secretKey = env('SECRET_KEY', 'tourtravelstore');
        $cipher = 'AES-256-CBC';

        // Thay thế lại các ký tự URL-safe thành ký tự gốc trong base64
        $data = str_replace(['-', '_', ''], ['+', '/', '='], $data);

        // Thêm padding '=' nếu thiếu
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }

        // Giải mã base64
        $decoded = base64_decode($data);

        // Tách phần mã hóa và IV
        list($encryptedData, $iv) = explode('::', $decoded, 2);

        // Giải mã dữ liệu
        return openssl_decrypt($encryptedData, $cipher, $secretKey, 0, $iv);
    }
}