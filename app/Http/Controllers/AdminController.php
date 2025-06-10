<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function customerList(Request $request)
    {
        $search = $request->input('search');

        $query = User::where('role', 'customer');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.customers.customer-list', [
            'customers' => $customers,
            'search' => $search
        ]);
    }

    public function customerShow($id)
    {
        $customer = User::where('role', 'customer')->with('wallet')->findOrFail($id);

        // Lấy thống kê của khách hàng
        $stats = [
            'total_orders' => 0, // Có thể thêm sau khi có bảng orders
            'total_spent' => 0,  // Có thể tính từ transactions
            'wallet_balance' => $customer->wallet ? $customer->wallet->balance : 0,
            'join_date' => $customer->created_at,
            'last_login' => $customer->updated_at, // Có thể thêm field last_login_at
        ];

        return view('admin.customers.customer-show', compact('customer', 'stats'));
    }

    public function customerEdit($id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        return view('admin.customers.customer-edit', compact('customer'));
    }

    public function customerUpdate(Request $request, $id)
    {
        $customer = User::where('role', 'customer')->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $customer->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function customerDestroy($id)
    {
        try {
            $customer = User::where('role', 'customer')->findOrFail($id);

            // Kiểm tra xem khách hàng có wallet hay không
            if ($customer->wallet && $customer->wallet->balance > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete customer with remaining wallet balance.');
            }

            // Xóa wallet trước (nếu có)
            if ($customer->wallet) {
                $customer->wallet->delete();
            }

            // Xóa customer
            $customer->delete();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }
}
