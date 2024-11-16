<?php

namespace App\Http\Controllers;

use App\Models\HashSecret;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\VerifyRegister;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\SentMessage;
use Twilio\Rest\Client;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function user()
    {
        return response()->json(['message' => 'Authenticated']);
    }

    public function isValidEmail($username)
    {
        return filter_var($username, FILTER_VALIDATE_EMAIL);
    }

    public function isValidPhoneNumber($username)
    {
        if (substr($username, 0, 1) === '+') {
            if (strlen($username) != 12 || substr($username, 3, 1) == '0' || !preg_match('/^\d+$/', substr($username, 1))) {
                return false;
            }
            return true;
        } else {
            if (strlen($username) != 10 || !preg_match('/^\d+$/', $username) || $username[0] != '0') {
                return false;
            }
            return true;
        }
    }

    public function mainInformation(Request $request)
    {
        //gan nhu da day du
        try {
            $validatedData = $request->validate([
                'username' => 'required|string|max:255|min:10|regex:/^\S*$/',
                'password' => 'required|string|min:8|max:30|regex:/[a-z]/|regex:/[A-Z
                ]/|regex:/[0-9]/|regex:/[@$!%*?&]/',
                'role' => 'required|in:1,2,3',
            ], [
                // 'username.unique' => 'co roi',
                'username.required' => 'Account name is required.',
                'username.max' => 'Account name must be between 10 and 255 characters.',
                'username.min' => 'Account name must be between 10 and 255 characters.',
                'username.regex' => 'Account name cannot contain spaces.',
                'password.max' => 'The password must be between 8 and 30 characters.',
                'password.min' => 'The password must be between 8 and 30 characters.',
                'password.required' => 'Password is required.',
                'password.regex' => 'Password must include at least 1 lowercase letter, 1 uppercase letter, 1 special character, and 1 number.',
                'role.required' => 'Choose your permission.',
                'role.in' => 'Invalid permission.',
            ]);
            $users = User::getAllUsers();
            $usernameExists = User::usernameExists($validatedData['username']);
            if ($usernameExists) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'username' => ['this account already exits in the system']
                    ],
                ], 422);
            }


            $code = rand(10000, 99999);
            $username = $validatedData['username'];
            $totalChars = strlen($username);
            $digitCount = preg_match_all('/[0-9]/', $username);

            // if ($totalChars > 0 && ($digitCount / $totalChars) >= 0.8) {
            //     if (!$this->isValidPhoneNumber($validatedData['username'])) {
            //         return response()->json([
            //             'message' => 'Validation failed',
            //             'errors' => [
            //                 'username' => ['Phone number must be formatted as follows: 0123456789 or +84123456789.'],
            //             ],
            //         ], 422);
            //     }

            //     $phoneNumber = '0' . substr($username, 3);
            //     $phoneNumber2 = '+84' . substr($username, 1);
            //     $users = User::all();

            //     foreach ($users as $user) {
            //         if ($user->username == $phoneNumber || $user->username == $phoneNumber2) {
            //             return response()->json([
            //                 'message' => 'Validation failed',
            //                 'errors' => [
            //                     'username' => 'Phone number already exists.'
            //                 ],
            //             ], 422);
            //         }
            //     }


            // }
            //  else {
                if (!$this->isValidEmail($validatedData['username'])) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => [
                            'username' => ['Email must be formatted as follows: abc@gmail.com.']
                        ],
                    ], 422);
                }

                try {
                    Mail::send('emails.test', compact('code'), function($email) use ($validatedData, $code) {
                        $email->subject('Demo test mail');
                        $email->to($validatedData['username'], $code);
                    });

                    // Kiểm tra và xóa bản ghi cũ
                    $verification = VerifyRegister::userExists($validatedData['username']);
                    if ($verification) {
                        $verification->delete();
                    }
                        $newVerification = VerifyRegister::create([
                            'username' => $validatedData['username'],
                            'verification_code' => $code,
                        ]);

                }
                catch (\Exception $e) {
                    return response()->json([
                        'errors' => [
                            'username' => ['Cannot send email']
                        ],

                    ], 422);
                }

            // }


            return response()->json([
                'message' => 'Information sent validly',
                'swicth' => 'send code'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->validator->errors(),
            ], 422);
        }
    }
    public function sendCode(Request $request) {
        try {
            $validatedData = $request->validate([
                'confirmCode' => 'required|string|max:5|min:5|regex:/^\d+$/',
            ], [
                'confirmCode.required' => 'Confirmation code required',
                'confirmCode.max' => 'The confirmation code must be exactly 5 characters long',
                'confirmCode.min' => 'The confirmation code must be exactly 5 characters long',
                'confirmCode.regex' => 'Only numeric characters are allowed',
            ]);

            $username = $request->username;
            $code = $validatedData['confirmCode'];


            $verification = VerifyRegister::findByUsername($username);


            // if (!$verification) {
            //     return response()->json([
            //         'message' => 'No verification record found for this username',
            //         'username' => $code,
            //     ], 404);
            // }

            // Kiểm tra mã xác minh
            if ($verification->verification_code != $code) {
                return response()->json([
                    'errors' => [
                        'confirmCode' => ['Re-enter confirmation code']
                    ],
                ], 401);
            } else {
                $verification->delete();
                return response()->json([
                    'message' => 'Valid confirmation code',
                    'swicth' => 'more infomation'
                ], 201);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->validator->errors(),
            ], 422);
        }
    }

    public function RegistermoreInfomation(Request $request) {
        try {
            $validatedData = $request->validate([
                'day' => 'required|integer|between:1,31',
                'month' => 'required|integer|between:1,12',
                'year' => 'required|integer|lte:2025',
                'gender' => 'required|in:male,female,other',
            ], [
                'day.required' => 'Select birth day',
                'day.between' => 'Invalid day',
                'month.required' => 'Select month',
                'month.between' => 'Invalid month',
                'year.required' => 'Select birth year',
                'year.lte' => 'Invalid year',
                'year.required' => 'Select gender',
                'gender.in' => 'Invalid gender',
            ]);
            $day = str_pad($validatedData['day'], 2, '0', STR_PAD_LEFT);
            $month = str_pad($validatedData['month'], 2, '0', STR_PAD_LEFT);
            $year = str_pad($validatedData['year'], 4, '0', STR_PAD_LEFT);
            $dateString = "{$year}-{$month}-$day";
            $date = Carbon::createFromDate($validatedData['year'], $validatedData['month'], $validatedData['day']);
            $dateStringz = $date->format('Y-m-d');
            if($dateStringz != $dateString) {
                return response()->json([
                    'errors' => [
                        'day' => ['Invalid day'],
                        'month' => ['Invalid month'],
                        'year' => ['Invalid year'],
                    ],
                ], 422);
            }
            $mainInfo = User::createUser($request);

            $idUser = $mainInfo->id;
            $moreInfomation = UserDetail::createUserDetail($date,$validatedData,$idUser);

            if($mainInfo && $moreInfomation) {
                return response()->json([
                    'message' => 'created account',
                    'data1' => $mainInfo,
                    'data2' => $moreInfomation
                ], 200);
            }
          } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation faied',
                'errors' => $e->validator->errors(),
            ], 422);
        }
    }
    public function registerMainInfo(Request $request) {
        $mainInfo = User::create([
            'username' => $request->username,
            'password' => hash::make($request->password),
            'role' => $request->role,
        ]);
        $mainInfoDetail = UserDetail::create([
            'user_id' => $mainInfo->id,
        ]);
        if($mainInfo && $mainInfoDetail) {
            return response()->json([
                'message' => 'created account',
                'data1' => $mainInfo,
                'data1' => $mainInfoDetail,
            ], 200);
        }
    }
    public function login(Request $request) {
        try {
            $validatedData = $request->validate([
                'username' => 'required|string|max:255|min:10|regex:/^\S*$/',
                'password' => 'required|string|min:8|max:30|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|',
                'role' => 'required|in:1,2,3',
            ], [
                'username.required' => 'Account Name required',
                'username.max' => 'Account must be between 10 and 255 characters',
                'username.min' => 'Account must be between 10 and 255 characters',
                'username.regex' => 'Account can not contain spaces',
                'password.required' => 'Password required.',
                'password.max' => 'The password must be between 8 and 30 characters.',
                'password.min' => 'The password must be between 8 and 30 characters.',
                'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
                'role.required' => 'Choose your permission.',
                'role.in' => 'Invalid permission.',
            ]);

            $username = $validatedData['username'];
            $totalChars = strlen($username);
            $digitCount = preg_match_all('/[0-9]/', $username);

            // if ($totalChars > 0 && ($digitCount / $totalChars) >= 0.8) {
            //     if (!$this->isValidPhoneNumber($validatedData['username'])) {
            //         return response()->json([
            //             'message' => 'Validation failed',
            //             'errors' => [
            //                 'username' => ['Phone number must be formatted as follows: 0123456789 or +84123456789.'],
            //             ],
            //         ], 422);
            //     }


            // } else {
                if (!$this->isValidEmail($validatedData['username'])) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => [
                            'username' => ['Email must be formatted as follows: abc@gmail.com.']
                        ],
                ], 422);
            }
            // }

            if (preg_match('/\s/', $validatedData['password'])) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'password' => ['The password must not contain spaces.']
                    ]
                ], 422);
            }

            if(!Auth::attempt($request->only('username','password','role'))) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'username' => ['Account name not found.']
                    ]
                ], 422);
             } else {
                 $user = Auth::user();
                 $token = $user->createToken('token')->plainTextToken;
                //  $secretKey = 'dat123';
                //  $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                //  $encryptedToken = openssl_encrypt($token, 'aes-256-cbc', $secretKey, 0, $iv);
                //  $encryptedToken = base64_encode($iv . $encryptedToken);
                 return response([
                     'message' => 'login sussesfully',
                     'token' => $token,
                    //  'user' => $user,
                   ],200);
                }

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->validator->errors(),
            ], 422);
        }
        catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }


    }
    public function inforCurrentUser(Request $request) {
        try {
            $user = Auth::user();


            return response()->json([
                'message' => 'Get data successfully',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User not found',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}