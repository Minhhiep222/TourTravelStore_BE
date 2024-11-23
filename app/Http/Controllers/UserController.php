<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json([
            'users'  => $users
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
    
        return response()->json([
            'message' => 'Người dùng đã được xóa thành công.'
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|max:100',
                'username' => [
                    'required', 
                    'max:100', 
                    'unique:users',
                    'not_regex:/^\s/', 
                    'not_regex:/\s{2,}/'
                ],
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'role' => 'required|in:1,2,3',
            ], [
                'name.required' => 'Vui lòng nhập tên.',
                'username.unique' => 'Tên người dùng đã tồn tại.',
                'email.required' => 'Vui lòng nhập email.',
                'email.email' => 'Định dạng email không hợp lệ.',
                'email.unique' => 'Email đã tồn tại.',
                'username.not_regex' => 'Không được có khoảng trắng đầu hoặc hai khoảng trắng liên tiếp.',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
                'role.in' => 'Vai trò không hợp lệ.',
            ]);
    
            $user = User::create([
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
                'role' => $validatedData['role'],
            ]);
    
            return response()->json([
                'message' => 'Người dùng đã được thêm thành công.',
                'user' => $user
            ], 201);
    
        } catch (\Exception $e) {
            \Log::error('Lỗi khi thêm người dùng: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi thêm người dùng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Tìm người dùng theo ID
            $user = User::find($id);
            
            // Kiểm tra xem người dùng có tồn tại không
            if (!$user) {
                return response()->json([
                    'message' => 'Người dùng không tồn tại.'
                ], 404); // Trả về mã 404 nếu không tìm thấy người dùng
            }
    
            $validatedData = $request->validate([
                'name' => 'required|max:100',
                'username' => [
                    'required', 
                    'max:100', 
                    'regex:/[a-zA-Z]/',
                    'not_regex:/^\s/',
                    'not_regex:/\s{2,}/',
                ],
                'email' => 'required|email|unique:users,email,' . $user->id,
                'password' => 'nullable|min:6',
                'role' => 'required|in:1,2,3',
            ], [
                'name.required' => 'Vui lòng nhập tên.',
                'username.regex' => 'Tên người dùng phải chứa ít nhất một chữ cái.',
                'email.required' => 'Vui lòng nhập email.',
                'email.email' => 'Định dạng email không hợp lệ.',
                'email.unique' => 'Email đã tồn tại.',
                'username.not_regex' => 'Tên người dùng không được có khoảng trắng đầu hoặc hai khoảng trắng liên tiếp.',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
                'role.in' => 'Vai trò không hợp lệ.',
            ]);
    
            // Nếu mật khẩu không được cung cấp, giữ nguyên mật khẩu cũ
            if ($request->filled('password')) {
                $validatedData['password'] = bcrypt($validatedData['password']);
            } else {
                unset($validatedData['password']);
            }
    
            // Cập nhật thông tin người dùng
            $user->update($validatedData);
    
            return response()->json([
                'message' => 'Người dùng đã được cập nhật thành công.',
            ], 200);
    
        } catch (\Exception $e) {
            \Log::error('Lỗi khi cập nhật người dùng: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi cập nhật người dùng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}