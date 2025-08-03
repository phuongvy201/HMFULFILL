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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Lấy tỷ giá từ cache hoặc gọi API nếu cache hết hạn
        $rate = Cache::remember('usd_vnd_rate', now()->addWeek(), function () {
            try {
                $apiKey = 'c2fe3e4ddf9e80e8261f52b9';
                $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/pair/USD/VND";

                $response = Http::get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    $rate = $data['conversion_rate'] ?? null;

                  

                    return $rate;
                } else {
                    Log::error('Failed to fetch exchange rate, using fallback rate', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);

                    return 26300; // Fallback rate
                }
            } catch (\Exception $e) {
                Log::error('Error fetching exchange rate, using fallback rate', [
                    'error' => $e->getMessage()
                ]);

                return 26300; // Fallback rate
            }
        });

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
            ->paginate(10);

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
                'transaction_code.unique' => 'Transaction code already exists.',
                'amount.required' => 'Please enter the amount.',
                'proof_image.image' => 'File uploaded must be an image.',
                'proof_image.mimes' => 'Image must be in JPEG, PNG or JPG format.',
                'proof_image.max' => 'Image size cannot exceed 2MB.'
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

                  
                } else {
                   
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

                // Gửi email ngay lập tức thay vì queue
                try {
                    Mail::to(config('mail.admin.address'))->send(new NewTopupRequest($transaction));
                   
                } catch (\Exception $e) {
                    Log::warning('Failed to send email notification', ['error' => $e->getMessage()]);
                    // Không throw exception để không làm fail transaction
                }

               

                return response()->json([
                    'message' => 'Topup request successful. Please wait for confirmation.'
                ]);
            } catch (\Exception $e) {
                Log::error('Create transaction failed', [
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
                   
                }

                return response()->json([
                    'message' => 'Have error when create transaction. Please try again.'
                ], 500);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Topup request failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Have error when topup.'
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
       

        return view('admin.topup.topup-request', [
            'topupRequests' => $topupRequests
        ]);
    }

    public function approveTopup($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Kiểm tra trạng thái trước khi xử lý
            if ($transaction->status !== Transaction::STATUS_PENDING) {
                Log::warning('Attempted to approve non-pending transaction', [
                    'transaction_id' => $id,
                    'current_status' => $transaction->status,
                    'admin_id' => Auth::id()
                ]);

                return redirect()->back()
                    ->with('error', 'Transaction has already been processed.');
            }

            DB::beginTransaction();

            // Cập nhật trạng thái transaction
            $transaction->update([
                'status' => Transaction::STATUS_APPROVED,
                'approved_at' => now(),
                'approved_by' => Auth::id()
            ]);

            // Cập nhật wallet
            $wallet = Wallet::where('user_id', $transaction->user_id)->first();
            if (!$wallet) {
                $wallet = new Wallet([
                    'user_id' => $transaction->user_id,
                    'balance' => 0
                ]);
                $wallet->save();
            }
            $wallet->deposit($transaction->amount);

            DB::commit();

            // Gửi email thông báo
            try {
                Mail::to($transaction->user->email)->send(new TopupApproved($transaction));
            } catch (\Exception $e) {
                Log::warning('Failed to send approval email', ['error' => $e->getMessage()]);
            }

           

            return redirect()->back()
                ->with('success', 'Topup request approved successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error approving topup transaction', [
                'error' => $e->getMessage(),
                'transaction_id' => $id,
                'admin_id' => Auth::id()
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

            // Gửi email thông báo cho user ngay lập tức
            try {
                Mail::to($transaction->user->email)->send(new TopupRejected($transaction));
            } catch (\Exception $e) {
                Log::warning('Failed to send rejection email', ['error' => $e->getMessage()]);
            }

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
                'amount.required' => 'Please enter the amount.',
                'amount.numeric' => 'The amount must be a number.',
                'amount.min' => 'The amount must be greater than 0.',
                'type.required' => 'Please select the transaction type.',
                'note.max' => 'Note cannot exceed 500 characters.'
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
                'note' => $request->note ?? 'Adjusted by Admin',
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
                $message = 'Successfully deposit money to account.';
            } else {
                if (!$wallet || !$wallet->hasEnoughBalance($request->amount)) {
                    return redirect()->back()->with('error', 'Insufficient balance to perform this transaction.');
                }
                $wallet->withdraw($request->amount);
                $message = 'Successfully deduct money from account.';
            }

           

            return redirect()->back()->with('success', $message);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error adjusting user balance', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            return redirect()->back()->with('error', 'Have error when adjusting balance. Please try again.');
        }
    }

    public function balanceOverview(Request $request)
    {
        $search = $request->input('search');

        $query = User::with('wallet');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$search}%"]);
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

       

        return view('admin.topup.balance-overview', [
            'users' => $users,
            'search' => $search
        ]);
    }

    /**
     * Refund a transaction
     */
    public function refundTransaction(Request $request, $id)
    {
        try {
            $request->validate([
                'refund_note' => 'nullable|string|max:500'
            ]);

            $transaction = Transaction::findOrFail($id);

            if (!$transaction->canBeRefunded()) {
                return redirect()->back()
                    ->with('error', 'Giao dịch này không thể được hoàn tiền.');
            }

            $refundTransaction = $transaction->refund(
                Auth::id(),
                $request->refund_note
            );

                    

            return redirect()->back()
                ->with('success', 'Giao dịch đã được hoàn tiền thành công.');
        } catch (\Exception $e) {
            Log::error('Error refunding transaction', [
                'error' => $e->getMessage(),
                'transaction_id' => $id,
                'admin_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi hoàn tiền: ' . $e->getMessage());
        }
    }
}
