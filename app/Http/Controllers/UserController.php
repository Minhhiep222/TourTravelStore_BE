<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::getAllUsers();
        return response()->json(['users' => $users], 200);
    }

    public function store(Request $request)
    {
        try {
            $user = User::createUser($request);
            return response()->json([
                'message' => 'Người dùng đã được thêm thành công.',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi thêm người dùng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $response = User::updateUser($request, $id);
            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi cập nhật người dùng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $response = User::deleteUser($id);
            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi xóa người dùng.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
