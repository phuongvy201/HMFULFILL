<?php

namespace App\Services;

use App\Models\ExcelOrder;
use App\Models\ExcelOrderItem;
use App\Models\ExcelOrderMockup;
use App\Models\ExcelOrderDesign;
use App\Models\ImportFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelOrderImportService
{
    private function getPositionTitle(string $position): string
    {
        return match ($position) {
            'Front' => 'Printing Front Side',
            'Back' => 'Printing Back Side',
            'Left sleeve' => 'Printing Left Sleeve Side',
            'Right sleeve' => 'Printing Right Sleeve Side',
            'Hem' => 'Printing Hem Side'
        };
    }

    private function processUrls(array $urls, string $position): array
    {
        if (empty($urls)) {
            return [];
        }

        $title = $this->getPositionTitle($position);
        return array_map(fn($url) => [
            'title' => $title,
            'url' => $url
        ], array_filter($urls));
    }

    public function process(ImportFile $importFile, array $rows)
    {
        $ordersByExternalId = [];

        foreach ($rows as $row) {
            // Bỏ qua dòng nếu không có external_id hoặc part_number
            if (empty($row['A']) && empty($row['Q'])) {
                continue;
            }

            $externalId = $row['A'] ?? '';

            // Tìm hoặc tạo order dựa vào external_id
            if (!isset($ordersByExternalId[$externalId])) {
                $ordersByExternalId[$externalId] = ExcelOrder::create([
                    'external_id' => $externalId,
                    'brand' => $row['B'] ?? '',
                    'channel' => $row['C'] ?? '',
                    'buyer_email' => $row['D'] ?? '',
                    'first_name' => $row['E'] ?? '',
                    'last_name' => $row['F'] ?? '',
                    'company' => $row['G'] ?? '',
                    'address1' => $row['H'] ?? '',
                    'address2' => $row['I'] ?? '',
                    'city' => $row['J'] ?? '',
                    'county' => $row['K'] ?? '',
                    'post_code' => $row['L'] ?? '',
                    'country' => $row['M'] ?? '',
                    'phone1' => $row['N'] ?? '',
                    'phone2' => $row['O'] ?? '',
                    'comment' => $row['P'] ?? '',
                    'status' => 'pending',
                    'import_file_id' => $importFile->id
                ]);
            }

            $order = $ordersByExternalId[$externalId];

            // Tạo order item cho sản phẩm mới
            $orderItem = ExcelOrderItem::create([
                'excel_order_id' => $order->id,
                'part_number' => $row['Q'] ?? '',
                'title' => $row['R'] ?? '',
                'quantity' => (int)($row['S'] ?? 0),
                'description' => $row['T'] ?? '',
                'label_name' => $row['U'] ?? '',
                'label_type' => $row['V'] ?? '',
            ]);

            // Reset các mảng cho mỗi sản phẩm
            $positions = [];
            $mockupUrls = [];
            $designUrls = [];

            // Xử lý các vị trí in và URL tương ứng
            $positionCols = ['W', 'Z', 'AC', 'AF', 'AI'];
            $mockupCols   = ['X', 'AA', 'AD', 'AG', 'AJ'];
            $designCols   = ['Y', 'AB', 'AE', 'AH', 'AK'];

            for ($i = 0; $i < 5; $i++) {
                $positionCol = $positionCols[$i];
                $mockupCol   = $mockupCols[$i];
                $designCol   = $designCols[$i];

                if (!empty($row[$positionCol])) {
                    $positions[] = $row[$positionCol];
                    $mockupUrls[] = $row[$mockupCol] ?? '';
                    $designUrls[] = $row[$designCol] ?? '';
                }
            }

            // Tạo mockup và design tương ứng
            foreach ($positions as $index => $position) {
                if (!empty($mockupUrls[$index])) {
                    ExcelOrderMockup::create([
                        'excel_order_item_id' => $orderItem->id,
                        'title' => $this->getPositionTitle($position),
                        'url' => $mockupUrls[$index]
                    ]);
                }

                if (!empty($designUrls[$index])) {
                    ExcelOrderDesign::create([
                        'excel_order_item_id' => $orderItem->id,
                        'title' => $this->getPositionTitle($position),
                        'url' => $designUrls[$index]
                    ]);
                }
            }
        }
    }
}
