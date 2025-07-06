<?php

require_once 'vendor/autoload.php';

use Carbon\Carbon;

// Test function để kiểm tra logic getStartDate
function getStartDate($period)
{
    switch ($period) {
        case 'day':
            return Carbon::now()->startOfDay();
        case 'week':
            return Carbon::now()->subDays(7)->startOfDay();
        case 'month':
            return Carbon::now()->subDays(30)->startOfDay();
        case 'year':
            return Carbon::now()->subDays(365)->startOfDay();
        default:
            return Carbon::now()->subDays(30)->startOfDay();
    }
}

// Test các period khác nhau
echo "=== Test Period Filter Logic ===\n\n";

$periods = ['day', 'week', 'month', 'year'];

foreach ($periods as $period) {
    $startDate = getStartDate($period);
    $now = Carbon::now();
    $diffInDays = $now->diffInDays($startDate);

    echo "Period: $period\n";
    echo "Start Date: " . $startDate->format('Y-m-d H:i:s') . "\n";
    echo "Current Date: " . $now->format('Y-m-d H:i:s') . "\n";
    echo "Difference: $diffInDays days\n";
    echo "---\n";
}

echo "\n=== Test URL Generation ===\n";
echo "Base URL: /admin/statistics/dashboard\n";
echo "With period=day: /admin/statistics/dashboard?period=day\n";
echo "With period=week: /admin/statistics/dashboard?period=week\n";
echo "With period=month: /admin/statistics/dashboard?period=month\n";
echo "With period=year: /admin/statistics/dashboard?period=year\n";

echo "\n=== Test Request Input ===\n";
echo "Expected: \$request->input('period', 'month')\n";
echo "This will get 'period' parameter from request, default to 'month' if not found\n";
