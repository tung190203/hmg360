<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('web')->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable',
            'current_password' => 'nullable|string',
            'password' => ['nullable', 'string', 'min:8', 'regex:/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>]).+$/'],
        ];

        $messages = [
            'name.required' => 'Họ và tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại trên hệ thống.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất 1 chữ viết hoa và 1 ký tự đặc biệt.',
        ];

        $validated = $request->validate($rules, $messages);

        // Check current password if attempting to change password
        if ($request->filled('password')) {
            if (!$request->filled('current_password')) {
                return response()->json([
                    'errors' => ['current_password' => ['Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu mới.']]
                ], 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'errors' => ['current_password' => ['Mật khẩu hiện tại không chính xác.']]
                ], 422);
            }
        }

        $user->name = trim(strip_tags($request->name));
        $user->email = trim(strip_tags($request->email));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Handle Avatar
        if ($request->filled('avatar') && preg_match('/^data:image\/(\w+);base64,/', $request->avatar, $type)) {
            $avatarData = substr($request->avatar, strpos($request->avatar, ',') + 1);
            $type = strtolower($type[1]); // png, jpg, jpeg

            $avatarData = base64_decode($avatarData);
            if ($avatarData !== false) {
                // Xóa avatar cũ
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $fileName = 'avatars/' . uniqid() . '.' . $type;
                Storage::disk('public')->put($fileName, $avatarData);
                $user->avatar = $fileName;
            }
        } else if ($request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            $fileName = 'avatars/' . time() . '_' . $file->getClientOriginalName();
            Storage::disk('public')->put($fileName, file_get_contents($file));

            // Xóa avatar cũ
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $fileName;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin cá nhân thành công!',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : asset('backend_assets/images/logo.png'),
            ]
        ]);
    }
}
