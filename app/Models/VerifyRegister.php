<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifyRegister extends Model
{
    use HasFactory;
    protected $table = 'verify_register';
    protected $fillable = ['username', 'verification_code'];
    
    public static function userExists($username)
    {
        return self::where('username', $username)->first();
    }
    public static function findByUsername($username)
    {
        return self::where('username', $username)->first();
    }

}
