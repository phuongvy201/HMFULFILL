<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewTopupRequest;
use Illuminate\Validation\ValidationException;
use App\Models\Wallet;
use App\Mail\TopupApproved;
use App\Mail\TopupRejected;
use App\Models\User;
use Aws\S3\S3Client;

class FinanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Lấy tỉ giá USD/VND từ ExchangeRate-API
        $apiKey = '613a32f493e22a04a52a41a8';
        $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/pair/USD/VND";

        $rate = null;
        $response = Http::get($url);
        if ($response->successful()) {
            $data = $response->json();
            $rate = $data['conversion_rate'] ?? null;
        }

        // Lấy thông tin wallet của user
        $wallet = Wallet::where('user_id', $user->id)->first();

        // Tính toán các giá trị balance
        $totalBalance = $wallet ? $wallet->getTotalBalance() : 0;
        $availableBalance = $wallet ? $wallet->getAvailableBalance() : 0;
        $holdAmount = $wallet ? $wallet->getHoldAmount() : 0;
        $creditAmount = $wallet ? $wallet->getCreditAmount() : 0;

        // Lấy lịch sử giao dịch của user
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Sử dụng phân trang với 10 giao dịch mỗi trang

        return view('customer.finance.wallet', [
            'usdToVndRate' => $rate,
            'userId' => $user->id,
            'totalBalance' => $totalBalance,
            'availableBalance' => $availableBalance,
            'holdAmount' => $holdAmount,
            'creditAmount' => $creditAmount,
            'transactions' => $transactions
        ]);
    }
    public function topup(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'transaction_code' => 'required|string|unique:transactions,transaction_code',
                'amount' => 'required|numeric|min:10',
                'method' => 'required|string|in:Bank Vietnam,Payoneer,PingPong,LianLian,Worldfirst,Paypal',
                'proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ], [
                'transaction_code.unique' => 'Mã giao dịch đã tồn tại.',
                'amount.required' => 'Vui lòng nhập số tiền.',
                'proof_image.image' => 'File tải lên phải là hình ảnh.',
                'proof_image.mimes' => 'Hình ảnh phải là định dạng JPEG, PNG hoặc JPG.',
                'proof_image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.'
            ]);

            Log::info('Yêu cầu topup được xác thực', [
                'user_id' => Auth::id(),
                'data' => $validated
            ]);

            $imageUrl = '';
            $filePath = '';

            try {
                if ($request->hasFile('proof_image')) {
                    $file = $request->file('proof_image');
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = 'proofs/' . $fileName;

                    // Lưu file vào thư mục public/proofs
                    $file->move(public_path('proofs'), $fileName);

                    // Tạo URL cục bộ cho hình ảnh
                    $imageUrl = asset('proofs/' . $fileName);

                    Log::info('Image uploaded successfully to local storage', [
                        'path' => $filePath,
                        'url' => $imageUrl
                    ]);
                } else {
                    Log::info('No image uploaded');
                }
            } catch (\Exception $e) {
                Log::error('Error uploading image to local storage: ' . $e->getMessage());
                // Xử lý lỗi nếu cần
                return response()->json([
                    'error' => 'Failed to upload image to local storage: ' . $e->getMessage()
                ], 500);
            }

            // Map phương thức thanh toán
            $methodMap = [
                'Bank Vietnam' => Transaction::METHOD_VND,
                'Payoneer' => Transaction::METHOD_PAYPAL,
                'PingPong' => Transaction::METHOD_PINGPONG,
                'LianLian' => Transaction::METHOD_LIANLIANPAY,
                'Worldfirst' => Transaction::METHOD_WORLDFIRST,
                'Paypal' => Transaction::METHOD_PAYPAL_NEW
            ];

            try {
                // Tạo giao dịch
                $transaction = Transaction::create([
                    'user_id' => Auth::id(),
                    'transaction_code' => $request->transaction_code,
                    'type' => Transaction::TYPE_TOPUP,
                    'method' => $methodMap[$request->method],
                    'amount' => $request->amount,
                    'status' => Transaction::STATUS_PENDING,
                    'note' => $imageUrl
                ]);

                // Gửi email thông báo
                Mail::to(config('mail.admin.address'))->queue(new NewTopupRequest($transaction));

                Log::info('Giao dịch được tạo và thông báo đã gửi', [
                    'transaction_id' => $transaction->id,
                    'transaction_code' => $transaction->transaction_code
                ]);

                return response()->json([
                    'message' => 'Yêu cầu topup thành công. Vui lòng đợi xác nhận.'
                ]);
            } catch (\Exception $e) {
                Log::error('Tạo giao dịch thất bại', [
                    'error' => $e->getMessage(),
                    'data' => [
                        'user_id' => Auth::id(),
                        'transaction_code' => $request->transaction_code,
                        'method' => $request->method,
                        'amount' => $request->amount
                    ]
                ]);

                // Xóa hình ảnh khỏi thư mục public nếu tạo giao dịch thất bại
                if ($imageUrl && file_exists(public_path($filePath))) {
                    unlink(public_path($filePath));
                    Log::info('Xóa hình ảnh khỏi thư mục public sau khi tạo giao dịch thất bại', [
                        'path' => $filePath
                    ]);
                }

                return response()->json([
                    'message' => 'Lỗi khi tạo giao dịch. Vui lòng thử lại.'
                ], 500);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Yêu cầu topup thất bại', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Có lỗi xảy ra khi gửi yêu cầu topup.'
            ], 500);
        }
    }

    public function topupRequests()
    {
        // Lấy toàn bộ yêu cầu topup từ tất cả user, kèm theo thông tin user
        $topupRequests = Transaction::where('type', Transaction::TYPE_TOPUP)
            ->with('user') // Eager load thông tin user
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Log danh sách các transaction
        Log::info('Topup requests retrieved', [
            'total' => $topupRequests->total(),
            'per_page' => $topupRequests->perPage(),
            'current_page' => $topupRequests->currentPage(),
            'transactions' => $topupRequests->items()
        ]);

        return view('admin.topup.topup-request', [
            'topupRequests' => $topupRequests
        ]);
    }

    public function approveTopup($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Kiểm tra nếu transaction đã được xử lý trước đó
            if ($transaction->status != Transaction::STATUS_PENDING) {
                return redirect()->back()
                    ->with('error', 'Transaction has already been processed.');
            }

            // Cập nhật trạng thái transaction thành approved
            $transaction->update([
                'status' => Transaction::STATUS_APPROVED,
                'approved_at' => now(),
                'approved_by' => Auth::id()
            ]);

            // Cập nhật balance cho user thông qua Wallet
            $user = $transaction->user;
            $wallet = $user->wallet; // Giả sử có quan hệ wallet được định nghĩa trong model User
            if (!$wallet) {
                // Nếu chưa có wallet, tạo mới
                $wallet = new Wallet([
                    'user_id' => $user->id,
                    'balance' => 0
                ]);
                $wallet->save();
            }
            $wallet->deposit($transaction->amount); // Sử dụng phương thức deposit từ Wallet model

            Log::info('Topup transaction approved', [
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'amount' => $transaction->amount,
                'approved_by' => Auth::id()
            ]);

            // Gửi email thông báo cho user
            Mail::to($user->email)->queue(new TopupApproved($transaction));

            return redirect()->back()
                ->with('success', 'Topup request approved successfully.');
        } catch (\Exception $e) {
            Log::error('Error approving topup transaction', [
                'error' => $e->getMessage(),
                'transaction_id' => $id
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred while approving the topup request.');
        }
    }

    public function rejectTopup($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Kiểm tra nếu transaction đã được xử lý trước đó
            if ($transaction->status != Transaction::STATUS_PENDING) {
                return redirect()->back()
                    ->with('error', 'Transaction has already been processed.');
            }

            // Cập nhật trạng thái transaction thành rejected
            $transaction->update([
                'status' => Transaction::STATUS_REJECTED,
                'approved_at' => now(),
                'approved_by' => Auth::id()
            ]);

            Log::info('Topup transaction rejected', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'rejected_by' => Auth::id()
            ]);

            // Gửi email thông báo cho user
            Mail::to($transaction->user->email)->queue(new TopupRejected($transaction));

            return redirect()->back()
                ->with('success', 'Topup request rejected successfully.');
        } catch (\Exception $e) {
            Log::error('Error rejecting topup transaction', [
                'error' => $e->getMessage(),
                'transaction_id' => $id
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred while rejecting the topup request.');
        }
    }

    public function userBalance($userId)
    {
        $user = User::findOrFail($userId);
        $wallet = Wallet::where('user_id', $user->id)->first();

        // Lấy lịch sử giao dịch của user
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Tính toán các giá trị balance
        $totalBalance = $wallet ? $wallet->getTotalBalance() : 0;
        $availableBalance = $wallet ? $wallet->getAvailableBalance() : 0;
        $holdAmount = $wallet ? $wallet->getHoldAmount() : 0;

        return view('admin.topup.user-balance', [
            'user' => $user,
            'totalBalance' => $totalBalance,
            'availableBalance' => $availableBalance,
            'holdAmount' => $holdAmount,
            'transactions' => $transactions
        ]);
    }

    public function adjustBalance(Request $request, $userId)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'type' => 'required|in:topup,deduct',
                'note' => 'nullable|string|max:500'
            ], [
                'amount.required' => 'Vui lòng nhập số tiền.',
                'amount.numeric' => 'Số tiền phải là số.',
                'amount.min' => 'Số tiền phải lớn hơn 0.',
                'type.required' => 'Vui lòng chọn loại giao dịch.',
                'note.max' => 'Ghi chú không được vượt quá 500 ký tự.'
            ]);

            $user = User::findOrFail($userId);
            $wallet = Wallet::where('user_id', $user->id)->first();

            // Tạo transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'transaction_code' => 'ADMIN_' . strtoupper(uniqid()),
                'type' => $request->type,
                'method' => 'Admin Adjustment',
                'amount' => $request->amount,
                'status' => Transaction::STATUS_APPROVED,
                'note' => $request->note ?? 'Điều chỉnh số dư bởi Admin',
                'approved_at' => now(),
                'approved_by' => Auth::id()
            ]);

            // Điều chỉnh số dư
            if ($request->type === Transaction::TYPE_TOPUP) {
                if (!$wallet) {
                    $wallet = new Wallet([
                        'user_id' => $user->id,
                        'balance' => 0
                    ]);
                    $wallet->save();
                }
                $wallet->deposit($request->amount);
                $message = 'Nạp tiền vào tài khoản thành công.';
            } else {
                if (!$wallet || !$wallet->hasEnoughBalance($request->amount)) {
                    return redirect()->back()->with('error', 'Số dư không đủ để thực hiện giao dịch này.');
                }
                $wallet->withdraw($request->amount);
                $message = 'Trừ tiền khỏi tài khoản thành công.';
            }

            Log::info('Admin adjusted user balance', [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'type' => $request->type,
                'amount' => $request->amount,
                'transaction_id' => $transaction->id
            ]);

            return redirect()->back()->with('success', $message);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error adjusting user balance', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi điều chỉnh số dư. Vui lòng thử lại.');
        }
    }

    public function balanceOverview(Request $request)
    {
        $search = $request->input('search');

        $query = User::with('wallet');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        // Xử lý trường hợp người dùng không có wallet
        foreach ($users as $user) {
            if (!$user->wallet) {
                // Nếu không có wallet, tạo một wallet mới với số dư bằng 0
                $user->wallet = new Wallet([
                    'user_id' => $user->id,
                    'balance' => 0
                ]);
            }
        }

        Log::info('Admin accessed balance overview', [
            'admin_id' => Auth::id(),
            'search_term' => $search,
            'total_users' => $users->total(),
            'users' => $users
        ]);

        return view('admin.topup.balance-overview', [
            'users' => $users,
            'search' => $search
        ]);
    }
}
