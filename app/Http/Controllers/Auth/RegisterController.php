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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
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
            // Rate limiting: Giới hạn 5 lần đăng ký mỗi giờ từ cùng IP
            $key = 'registration:' . $request->ip();
            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                return redirect()->back()
                    ->withErrors(['error' => "Quá nhiều lần thử đăng ký. Vui lòng thử lại sau {$seconds} giây."])
                    ->withInput();
            }
            RateLimiter::hit($key, 3600); // 1 giờ

            // Validate the request
            $request->validate([
                'first_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
                'last_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'phone' => 'nullable|string|max:15|regex:/^[0-9]+$/|unique:users,phone',
                'g-recaptcha-response' => 'required|recaptcha', // Yêu cầu CAPTCHA
            ]);

            // Kiểm tra email domain có hợp lệ
            $emailDomain = substr(strrchr($request->email, "@"), 1);
            $disallowedDomains = ['tempmail.org', '10minutemail.com', 'guerrillamail.com', 'mailinator.com'];
            if (in_array(strtolower($emailDomain), $disallowedDomains)) {
                return redirect()->back()
                    ->withErrors(['email' => 'Email domain không được phép sử dụng.'])
                    ->withInput();
            }

            // Kiểm tra tên có vẻ hợp lệ (không phải random string)
            if ($this->isRandomString($request->first_name) || $this->isRandomString($request->last_name)) {
                return redirect()->back()
                    ->withErrors(['error' => 'Tên không hợp lệ.'])
                    ->withInput();
            }

            // Tạo verification token
            $verificationToken = Str::random(64);
            
            // Tạo người dùng mới (chưa verify email)
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => 'customer',
                'email_verified_at' => null, // Không verify ngay lập tức
                'email_verification_at' => $verificationToken, // Token để verify
                'api_token' => null, // Không tạo API token cho đến khi verify email
            ]);

            // Gửi email verification
            try {
                Mail::to($user->email)->send(new VerifyEmail($user));
                Log::info('Verification email sent', ['user_id' => $user->id, 'email' => $user->email]);
            } catch (\Exception $e) {
                Log::error('Failed to send verification email', ['error' => $e->getMessage()]);
                // Xóa user nếu không gửi được email
                $user->delete();
                return redirect()->back()
                    ->withErrors(['error' => 'Không thể gửi email xác thực. Vui lòng thử lại.'])
                    ->withInput();
            }

            // Log registration attempt
            Log::info('New user registration', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Chuyển hướng đến trang thông báo verify email
            return redirect()->route('signin')
                ->with('message', 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Registration validation error: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra. Vui lòng thử lại.'])->withInput();
        }
    }

    public function verifyEmail($token)
    {
        $user = User::where('email_verification_at', $token)->first();

        if ($user) {
            $user->email_verified_at = now();
            $user->email_verification_at = null; // Xóa token sau khi xác thực
            $user->api_token = Str::random(80); // Tạo API token sau khi verify
            $user->save();

            Log::info('Email verified successfully', ['user_id' => $user->id]);

            return redirect('/signin')->with('message', 'Email đã được xác thực thành công! Bạn có thể đăng nhập ngay bây giờ.');
        }
        
        Log::warning('Invalid verification token attempted', ['token' => $token]);
        return redirect('/signin')->with('error', 'Token xác thực không hợp lệ.');
    }

    public function showLoginForm()
    {
        return view('auth.signin');
    }

    public function login(Request $request)
    {
        // Rate limiting cho login
        $key = 'login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["Quá nhiều lần thử đăng nhập. Vui lòng thử lại sau {$seconds} giây."],
            ]);
        }

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            
            // Kiểm tra email đã được verify chưa
            if (!$user->email_verified_at) {
                Auth::logout();
                return redirect()->route('signin')
                    ->withErrors(['email' => 'Vui lòng xác thực email trước khi đăng nhập.']);
            }

            RateLimiter::clear($key); // Xóa rate limit khi đăng nhập thành công

            Log::info('User logged in successfully', ['user_id' => $user->id, 'ip' => $request->ip()]);

            if ($user->role === 'admin') {
                return redirect()->route('admin.statistics.dashboard');
            } elseif ($user->role === 'customer') {
                return redirect()->route('customer.index');
            } elseif ($user->role === 'design') {
                return redirect()->route('designer.tasks.index');
            }
        }

        RateLimiter::hit($key, 900); // 15 phút

        Log::warning('Failed login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        throw ValidationException::withMessages([
            'email' => ['Thông tin đăng nhập không chính xác.'],
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            Log::info('User logged out', ['user_id' => $user->id]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('signin');
    }

    /**
     * Kiểm tra xem string có phải là random string không
     */
    private function isRandomString($string)
    {
        // Kiểm tra độ dài quá ngắn hoặc quá dài
        if (strlen($string) < 2 || strlen($string) > 50) {
            return true;
        }

        // Kiểm tra có chứa ký tự đặc biệt không hợp lệ
        if (preg_match('/[^a-zA-Z\s]/', $string)) {
            return true;
        }

        // Kiểm tra có phải là chuỗi lặp lại không
        if (strlen($string) > 3 && $string === str_repeat(substr($string, 0, 1), strlen($string))) {
            return true;
        }

        return false;
    }
}
