<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserSpecificPricingImportService;
use App\Services\UserSpecificPricingService;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\ShippingPrice;

class ImportUserSpecificPricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:user-pricing 
                            {--file= : Path to CSV file}
                            {--user-id= : User ID to import for}
                            {--variant-id= : Variant ID to import for}
                            {--method= : Shipping method}
                            {--price= : Price}
                            {--currency=USD : Currency}
                            {--dry-run : Preview without importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import user-specific pricing from CSV file or command line';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->option('file');
        $userId = $this->option('user-id');
        $variantId = $this->option('variant-id');
        $method = $this->option('method');
        $price = $this->option('price');
        $currency = $this->option('currency');
        $dryRun = $this->option('dry-run');

        if ($file) {
            $this->importFromFile($file, $dryRun);
        } elseif ($userId && $variantId && $method && $price) {
            $this->importSingle($userId, $variantId, $method, $price, $currency, $dryRun);
        } else {
            $this->error('Please provide either --file option or all required options (--user-id, --variant-id, --method, --price)');
            return 1;
        }

        return 0;
    }

    /**
     * Import từ file CSV
     */
    private function importFromFile(string $filePath, bool $dryRun)
    {
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return;
        }

        $this->info("Reading file: {$filePath}");

        // Parse CSV file
        $data = [];
        $handle = fopen($filePath, 'r');

        if (!$handle) {
            $this->error("Cannot open file: {$filePath}");
            return;
        }

        // Đọc header
        $headers = fgetcsv($handle);

        if (!$headers) {
            $this->error("Invalid CSV file: no headers found");
            fclose($handle);
            return;
        }

        // Validate headers
        $requiredHeaders = ['user_email', 'variant_sku', 'method', 'price', 'currency'];
        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (!empty($missingHeaders)) {
            $this->error("Missing required headers: " . implode(', ', $missingHeaders));
            fclose($handle);
            return;
        }

        // Đọc data
        $rowCount = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= count($headers)) {
                $dataRow = array_combine($headers, $row);
                $data[] = $dataRow;
                $rowCount++;
            }
        }

        fclose($handle);

        $this->info("Found {$rowCount} rows in file");

        if ($dryRun) {
            $this->previewData($data);
            return;
        }

        // Validate dữ liệu
        $errors = UserSpecificPricingImportService::validateImportData($data);
        if (!empty($errors)) {
            $this->error("Validation errors:");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
            return;
        }

        // Import dữ liệu
        $this->info("Starting import...");
        $results = UserSpecificPricingImportService::importFromData($data);

        $this->displayResults($results);
    }

    /**
     * Import một giá đơn lẻ
     */
    private function importSingle(int $userId, int $variantId, string $method, float $price, string $currency, bool $dryRun)
    {
        // Validate user
        $user = User::find($userId);
        if (!$user) {
            $this->error("User not found with ID: {$userId}");
            return;
        }

        // Validate variant
        $variant = ProductVariant::find($variantId);
        if (!$variant) {
            $this->error("Product variant not found with ID: {$variantId}");
            return;
        }

        // Validate method
        if (!in_array($method, ShippingPrice::$validMethods)) {
            $this->error("Invalid method: {$method}. Valid methods: " . implode(', ', ShippingPrice::$validMethods));
            return;
        }

        // Validate price
        if ($price <= 0) {
            $this->error("Price must be positive: {$price}");
            return;
        }

        // Validate currency
        if (!in_array($currency, ['USD', 'VND', 'GBP'])) {
            $this->error("Invalid currency: {$currency}. Valid currencies: USD, VND, GBP");
            return;
        }

        if ($dryRun) {
            $this->info("DRY RUN - Would import:");
            $this->info("  User: {$user->email} ({$user->first_name} {$user->last_name})");
            $this->info("  Variant: {$variant->sku}");
            $this->info("  Method: {$method}");
            $this->info("  Price: {$price} {$currency}");
            return;
        }

        try {
            $shippingPrice = UserSpecificPricingService::setUserPrice(
                $userId,
                $variantId,
                $method,
                $price,
                $currency
            );

            $this->info("Successfully imported user-specific price:");
            $this->info("  User: {$user->email}");
            $this->info("  Variant: {$variant->sku}");
            $this->info("  Method: {$method}");
            $this->info("  Price: {$price} {$currency}");
        } catch (\Exception $e) {
            $this->error("Failed to import: " . $e->getMessage());
        }
    }

    /**
     * Preview dữ liệu
     */
    private function previewData(array $data)
    {
        $this->info("DRY RUN - Preview of data to import:");
        $this->info("");

        $headers = ['Row', 'User Email', 'Variant SKU', 'Method', 'Price', 'Currency'];
        $rows = [];

        foreach (array_slice($data, 0, 10) as $index => $row) {
            $rows[] = [
                $index + 1,
                $row['user_email'] ?? 'N/A',
                $row['variant_sku'] ?? 'N/A',
                $row['method'] ?? 'N/A',
                $row['price'] ?? 'N/A',
                $row['currency'] ?? 'N/A'
            ];
        }

        $this->table($headers, $rows);

        if (count($data) > 10) {
            $this->info("... and " . (count($data) - 10) . " more rows");
        }
    }

    /**
     * Hiển thị kết quả import
     */
    private function displayResults(array $results)
    {
        $this->info("");
        $this->info("Import completed!");
        $this->info("  Success: {$results['success']}");
        $this->info("  Failed: {$results['failed']}");
        $this->info("  Total: {$results['summary']['total_rows']}");

        if (!empty($results['errors'])) {
            $this->error("");
            $this->error("Errors:");
            foreach ($results['errors'] as $error) {
                $this->error("  Row {$error['row']}: " . json_encode($error['errors']));
            }
        }

        if (!empty($results['summary']['processed_users'])) {
            $this->info("");
            $this->info("Processed users:");
            foreach ($results['summary']['processed_users'] as $userData) {
                $this->info("  {$userData['user_name']} ({$userData['user_email']}): {$userData['count']} prices");
            }
        }
    }
}
