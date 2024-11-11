<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TourGuide extends Model
{
    use HasFactory;

   
    protected $table = 'tour_guides';

   
    protected $fillable = [
        'name',
        'email',
        'phone',
        'experience',
    ];
    private function encryptId($id, $key) {
        $method = 'AES-256-CBC';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
        $encryptedId = openssl_encrypt($id, $method, $key, 0, $iv);
        return base64_encode($iv . $encryptedId);
    }
    private function decryptId($encryptedId, $key) {
        $method = 'AES-256-CBC';
        $decodedUrl = urldecode($encryptedId);
        $decodedData = base64_decode($decodedUrl);
        $ivLength = openssl_cipher_iv_length($method);
        $iv = substr($decodedData, 0, $ivLength);
        $encryptedIdWithoutIv = substr($decodedData, $ivLength);
        
        return openssl_decrypt($encryptedIdWithoutIv, $method, $key, 0, $iv);
    }
    private function isValidEmail($username)
    {
        return filter_var($username, FILTER_VALIDATE_EMAIL);
    }
    public function isValidPhoneNumber($phone)
    {
        if (substr($phone, 0, 1) === '+') {
            if (strlen($phone) != 12 || substr($phone, 3, 1) == '0' || !preg_match('/^\d+$/', substr($phone, 1))) {
                return false;
            }
            return true;
        } else {
            if (strlen($phone) != 10 || !preg_match('/^\d+$/', $phone) || $phone[0] != '0') {
                return false;
            }
            return true;
        }
    }
    public function getAllEncryptedTourGuides($key) {
        $tourGuides = self::all(); 
        return $tourGuides->map(function($tour) use ($key) {
            return [
                'id' => $this->encryptId($tour->id, $key), 
                'name' => $tour->name, 
                'email' => $tour->email, 
                'phone' => $tour->phone, 
                'experience' => $tour->experience, 
            ];
        });
    }
    public function addTourGuide(array $data)
    {
        $validator = Validator::make($data, [
            'email' => 'required|string|max:255|min:10|regex:/^\S*$/|unique:tour_guides,email',
            'name' => 'required|string|max:255|min:5|regex:/^[\p{L}\s]+$/u',
            'phone' => 'required|min:10|unique:tour_guides,phone',
            'experience' => 'required|min:0|numeric',
        ], [
            'email.required' => 'Email is required.',
            'email.max' => 'Email must be between 10 and 255 characters.',
            'email.min' => 'Email must be between 10 and 255 characters.',
            'email.regex' => 'Email cannot contain spaces.',
            'email.unique' => 'This email already exists in the system.',
            'name.required' => 'Name is required.',
            'name.max' => 'Name must be between 5 and 255 characters.',
            'name.min' => 'Name must be between 5 and 255 characters.',
            'name.regex' => 'Name can only contain letters and spaces, and cannot include special characters.',
            'phone.required' => 'Phone is required.',
            'phone.min' => 'Phone number must be at least 10 characters',
            'phone.unique' => 'Phone number already exists.',
            'experience.required' => 'Experience is required.',
            'experience.numeric' => 'Experience must be a number.',
            'experience.min' => 'Experience must be at least 0.'
        ]);
    
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (isset($data['email']) && User::where('username', $data['email'])->exists()) {
            throw ValidationException::withMessages(['email' => 'This email already exists in the system.']);
        }
    
        if (!$this->isValidEmail($data['email'])) {
            throw ValidationException::withMessages(['email' => 'Email must be formatted as follows: abc@gmail.com.']);
        }
        
        if (!$this->isValidPhoneNumber($data['phone'])) {
            throw ValidationException::withMessages(['phone' => 'Phone number must be formatted as follows: 0123456789 or +84123456789.']);
        }
    
        // Check for existing phone number
        $phoneNumber = '0' . substr($data['phone'], 3);
        $phoneNumber2 = '+84' . substr($data['phone'], 1);
        if ($this->where('phone', $phoneNumber)->orWhere('phone', $phoneNumber2)->exists()) {
            throw ValidationException::withMessages(['phone' => 'Phone number already exists.']);
        }
        return self::create($data);
    }
    
    
    public function getTourGuideID($data, $key) {
        $validatedData = Validator::make($data, [
            'tour_id' => 'required',
        ], [
            'tour_id.required' => 'Tour ID is required.',
        ]);
    
        if ($validatedData->fails()) {
            throw new ValidationException($validatedData);
        }
    
        $encodedTourId = $validatedData->validated()['tour_id'];
        $tourId = $this->decryptId($encodedTourId, $key);
    
        if (!$tourId) {
            throw ValidationException::withMessages(['error' => "Invalid tour ID."]);
        }
    
        return self::find($tourId); 
    }
    public function deleteTourGuide($data, $key) {
        $validatedData = Validator::make($data, [
            'tour_id' => 'required',
        ], [
            'tour_id.required' => 'Tour ID is required.',
        ]);
    
      
        if ($validatedData->fails()) {
            throw new ValidationException($validatedData);
        }
        
        $validatedData = $validatedData->validated();
        $encodedTourId = $validatedData['tour_id'];
        $tourId = $this->decryptId($encodedTourId, $key);
        
        if (!$tourId) {
            throw ValidationException::withMessages(['error' => 'Invalid tour ID.']);
        }
    
        return $this->destroy($tourId);
    }
    public function updateTourGuide($data, $key)
    {
        $rules = [];
        $messages = [];
    
        if (isset($data['email'])) {
            $rules['email'] = 'string|max:255|min:10|regex:/^\S*$/|unique:tour_guides,email';
            $messages['email.max'] = 'Email must be between 10 and 255 characters.';
            $messages['email.min'] = 'Email must be between 10 and 255 characters.';
            $messages['email.regex'] = 'Email cannot contain spaces.';
            $messages['email.unique'] = 'This email already exists in the system.';
        }
    
        if (isset($data['name'])) {
            $rules['name'] = 'string|max:255|min:5|regex:/^[\p{L}\s]+$/u';
            $messages['name.max'] = 'Name must be between 5 and 255 characters.';
            $messages['name.min'] = 'Name must be between 5 and 255 characters.';
            $messages['name.regex'] = 'Name can only contain letters and spaces, and cannot include special characters.';
        }
    
        if (isset($data['phone'])) {
            $rules['phone'] = 'min:10|unique:tour_guides,phone';
            $messages['phone.required'] = 'Phone is required.';
            $messages['phone.min'] = 'Phone number must be at least 10 characters';
            $messages['phone.unique'] = 'Phone number already exists.';
        }
    
        if (isset($data['experience'])) {
            $rules['experience'] = 'numeric|min:0';
            $messages['experience.numeric'] = 'Experience must be a number.';
            $messages['experience.min'] = 'Experience must be at least 0.';
        }
    
        // Validate dữ liệu
        $validatedData = Validator::make($data, $rules, $messages);
    
        if ($validatedData->fails()) {
            throw new ValidationException($validatedData);
        }
    
       
        if (isset($data['email']) && User::where('username', $data['email'])->exists()) {
            throw ValidationException::withMessages(['email' => 'This email already exists in the system.']);
        }
    
      
        if (isset($data['email']) && !$this->isValidEmail($data['email'])) {
            throw ValidationException::withMessages(['email' => 'Email must be formatted as follows: abc@gmail.com.']);
        }
    
       
        if (isset($data['phone']) && !$this->isValidPhoneNumber($data['phone'])) {
            throw ValidationException::withMessages(['phone' => 'Phone number must be formatted as follows: 0123456789 or +84123456789.']);
        }
    
      
        if (isset($data['phone'])) {
            $phone = $data['phone'];
            $phoneNumber = '0' . substr($phone, 3);
            $phoneNumber2 = '+84' . substr($phone, 1);
    
            $existingPhone = self::where('phone', $phoneNumber)->orWhere('phone', $phoneNumber2)->first();
            if ($existingPhone) {
                throw ValidationException::withMessages(['phone' => 'Phone number already exists.']);
            }
        }
    
       
        $encodedTourId = $data['tour_id'];
        $tourId = $this->decryptId($encodedTourId, $key);
        if (!$tourId) {
            throw ValidationException::withMessages(['error' => 'Invalid tour ID.']);
        }
    
      
        $tourGuide = self::find($tourId);
        if (!$tourGuide) {
            throw ValidationException::withMessages(['error' => 'Tour guide not found']);
        }
    
      
        if (isset($data['name'])) {
            $tourGuide->name = $data['name'];
        }
        if (isset($data['email'])) {
            $tourGuide->email = $data['email'];
        }
        if (isset($data['phone'])) {
            $tourGuide->phone = $data['phone'];
        }
        if (isset($data['experience'])) {
            $tourGuide->experience = $data['experience'];
        }
    
        // Lưu thay đổi
        $tourGuide->save();
    
        return $tourGuide;
    }
}
