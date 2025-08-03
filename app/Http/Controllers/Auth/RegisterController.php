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
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
                'api_token' => Str::random(80), // Tạo API token ngẫu nhiên
            ]);

            // Chuyển hướng đến trang đăng nhập
            return redirect()->route('signin')->with('message', 'Registration successful! You can now log in.');
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

            return redirect('/signin')->with('message', 'Email verified successfully!');
        }
        return redirect('/signin')->with('error', 'Invalid verification token.');
    }

    public function showLoginForm()
    {
        return view('auth.signin');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            if ($user->role === 'admin') {
                return redirect()->route('admin.statistics.dashboard');
            } elseif ($user->role === 'customer') {
                return redirect()->route('customer.index');
            } elseif ($user->role === 'design') {
                return redirect()->route('designer.tasks.index');
            }
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('signin')->with('message', 'You have been logged out successfully.');
    }
}
