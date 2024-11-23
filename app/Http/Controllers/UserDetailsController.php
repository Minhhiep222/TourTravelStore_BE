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
            } else {
                // Nếu không có user details, bạn có thể tạo mới nếu cần
                $userDetails = UserDetails::createUserDetail($request->input('dob'), $request->all(), $user->id);
            }
    
            // Trả lại toàn bộ user với details
            return response()->json(['message' => 'User updated successfully', 'user' => $user->load('details')]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating user', 'error' => $e->getMessage()], 500);
        }
    }
}