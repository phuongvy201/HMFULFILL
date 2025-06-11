<?php

namespace App\Console\Commands;

use App\Models\ExcelOrder;
use Illuminate\Console\Command;

class AutoUpdateOrderStatus extends Command
{
    protected $signature = 'orders:update-status';
    protected $description = 'Update order status from on hold to pending after 1 hour';

    public function handle()
    {
        $orders = ExcelOrder::where('status', 'on hold')
            ->where('created_at', '<=', now()->subMinutes(1))
            ->get();

        foreach ($orders as $order) {
            $order->status = 'pending';
            $order->save();
        }

        $this->info('Order statuses updated successfully.');
    }
}
