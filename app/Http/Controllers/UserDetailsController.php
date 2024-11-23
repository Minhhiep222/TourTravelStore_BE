<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetails; 

class UserDetailsController extends Controller
{
    // Lấy thông tin người dùng
    public function show($id)
    {
        $user = User::with('details')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    // Cập nhật thông tin người dùng
    public function update(Request $request, $id)
    {
        // Validate dữ liệu
        // $validatedData = $request->validate([
        //     'name' => 'string|max:255',
        //     'email' => 'string|email|max:255',
        //     'gender' => 'in:male,female,other',
        //     'dobDay' => 'integer|between:1,31',
        //     'dobMonth' => 'integer|between:1,12',
        //     'dobYear' => 'integer',
        //     'phone' => 'string|max:15',
        //     'address' => 'string|max:255',
        //     'profile_picture' => 'string|max:255'
        // ]);

        // Tìm user
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        try {
            $user->update($request->only(['name', 'username', 'email', 'role']));
    
            $userDetails = $user->details;
            if ($userDetails) {
                $userDetails->update($request->only(['phone', 'address', 'profile_picture', 'gender', 'dob']));
            }
    
            return response()->json(['message' => 'User updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating user', 'error' => $e->getMessage()], 500);
        }
    }
}