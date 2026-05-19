<?php

namespace App\Http\Controllers;

use App\Http\Requests\Guest\LoginRequest;
use App\Http\Requests\Guest\RegisterRequest;
use App\Http\Requests\Guest\UpdateRequest;
use App\Models\Guest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
    
        if(Auth::guard('guest')->attempt($credentials, $request->filled('remember'))) {
            return response()->json([
                'success' => true,
                'redirect' => url()->previous() ?: url('/'),
            ]);
        }

        return response()->json(['message' => 'Thông tin đăng nhập không chính xác'], 401);
    }    

    public function register(RegisterRequest $request)
    {
        Guest::create([
            'name' => $request->input('name'),
            'identification_number' => $request->input('identification_number'),
            'email' => $request->input('email'),
            'nation_id' => $request->input('nation_id'),
            'password' => bcrypt($request->input('password')),
        ]);

        // Redirect to the intended page or home page
        return response()->json([
            'success' => true,
            'redirect' => url()->previous() ?: url('/'),
        ]);
    }
    public function logout(Request $request)
    {
        Auth::guard('guest')->logout();
        $request->session()->forget('guest');
        $request->session()->regenerateToken();
        return redirect()->intended(url()->previous() ?: '/');
    }

    public function updateAccount(UpdateRequest $request)
    {
        $guest = Auth::guard('guest')->user();
        // Update guest details
        $guest->name = $request->input('name');
        if($request->filled('identification_number')) {
            $guest->identification_number = $request->input('identification_number');
        }
        $guest->email = $request->input('email');
        $guest->nation_id = $request->input('nation_id');
        $guest->phone = $request->input('phone');
        $guest->address = $request->input('address');
        if($request->filled('password')) {
            $guest->password = Hash::make($request->input('password'));
        }
        if ($request->filled('avatar')) {
            $avatarData = $request->input('avatar');
    
            // format: "data:image/png;base64,xxxx"
            if (preg_match('/^data:image\/(\w+);base64,/', $avatarData, $type)) {
                $avatarData = substr($avatarData, strpos($avatarData, ',') + 1);
                $type = strtolower($type[1]); // png, jpg, jpeg
    
                $avatarData = base64_decode($avatarData);
                if ($avatarData === false) {
                    return back()->withErrors(['avatar' => 'Invalid image data']);
                }
    
                // Xóa avatar cũ
                if ($guest->avatar && Storage::disk('public')->exists($guest->avatar)) {
                    Storage::disk('public')->delete($guest->avatar);
                }
    
                $fileName = 'avatars/' . uniqid() . '.' . $type;
                Storage::disk('public')->put($fileName, $avatarData);
                $guest->avatar = $fileName;
            }
        }
        $guest->save();

        return redirect()->back()->with('success', 'Chỉnh sửa thông tin thành công');
    }
    public function redirectToGoogle()
    {
        $host = request()->getHost();
        $redirectUrl = ($host === 'localhost') 
            ? env('APP_URL') . ':' . env('APP_PORT', 80) . '/guest/auth/google/callback'
            : 'https://' . $host . '/guest/auth/google/callback';

        return Socialite::driver('google')->redirectUrl($redirectUrl)->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $host = request()->getHost();
            $redirectUrl = ($host === 'localhost') 
                ? env('APP_URL') . ':' . env('APP_PORT', 80) . '/guest/auth/google/callback'
                : 'https://' . $host . '/guest/auth/google/callback';
            $googleUser = Socialite::driver('google')->redirectUrl($redirectUrl)->user();
    
            $avatarPath = null;
    
            // Nếu Google có avatar thì tải về
            if ($googleUser->getAvatar()) {
                $avatarUrl = $googleUser->getAvatar();
                $avatarContents = file_get_contents($avatarUrl);
                $fileName = 'avatars/' . uniqid() . '.jpg';
                Storage::disk('public')->put($fileName, $avatarContents);
                $avatarPath = $fileName;
            }
    
            $guest = Guest::updateOrCreate(
                [ 'email' => $googleUser->getEmail() ], // tìm theo email
                [
                    'name'     => $googleUser->getName(),
                    'avatar'   => $avatarPath,
                    'password' => bcrypt(str()->random(16)),
                ]
            );
    
            if (!$guest->avatar && $avatarPath) {
                $guest->avatar = $avatarPath;
                $guest->save();
            }
    
            Auth::guard('guest')->login($guest, true);
    
            return redirect()->intended('/');
        } catch (\Exception $e) {
            return redirect()->route('guest_login')->withErrors(['msg' => 'Đăng nhập Google thất bại.']);
        }
    }
}
