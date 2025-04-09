<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use Illuminate\Support\Str;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register'); // Trả về view đăng ký
    }

    public function register(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:15|regex:/^[0-9]+$/|unique:users,phone', // Kiểm tra số điện thoại
            ]);

            // Tạo người dùng mới
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => 'customer',
                'email_verified_at' => now(), // Đánh dấu email là đã xác thực ngay lập tức
            ]);

            // Chuyển hướng đến trang đăng nhập
            return redirect()->route('login')->with('message', 'Registration successful! You can now log in.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Ghi log lỗi
            Log::error('Registration error: ' . $e->getMessage());

            // Trả về thông báo lỗi
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            // Ghi log lỗi
            Log::error('Registration error: ' . $e->getMessage());

            return redirect()->back()->withErrors(['error' => 'An error occurred.'])->withInput();
        }
    }


    public function verifyEmail($token)
    {
        $user = User::where('email_verification_at', $token)->first();

        if ($user) {
            $user->email_verified_at = now();
            $user->email_verification_at = null; // Xóa token sau khi xác thực
            $user->save();

            return redirect('/login')->with('message', 'Email verified successfully!');
        }

        return redirect('/login')->with('error', 'Invalid verification token.');
    }
}
