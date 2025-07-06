<?php

// Test script để kiểm tra logic xử lý tracking number từ DTF API

// Dữ liệu mẫu từ DTF API
$sampleDtfData = [
    [
        "id" => "7a5644d2-eb7d-4068-b974-c71e8e575977",
        "external_id" => "577015443550081512",
        "brand" => "HM Fulfill",
        "channel" => "tiktok",
        "buyer_email" => "customer@example.com",
        "shipping_address" => [
            "first_name" => "Jonah",
            "last_name" => "Robles",
            "company" => "",
            "address1" => "128 LAKE MONTEREY CIR",
            "address2" => "",
            "city" => "Boynton Beach",
            "state" => "Florida",
            "postcode" => "33426",
            "country" => "US",
            "phone1" => "",
            "phone2" => "(+1)5613198564"
        ],
        "items" => [
            [
                "id" => 4741,
                "product_name" => "M / WHITE-1 mặt",
                "quantity" => 1,
                "description" => "",
                "mockups" => [
                    [
                        "title" => "M-Back",
                        "src" => "https://p19-oec-ttp.tiktokcdn-us.com/tos-useast5-i-omjb5zjo8w-tx/9a193d2448104979a53a7c9f774077c1~tplv-omjb5zjo8w-origin-jpeg.jpeg?from=2178765313"
                    ]
                ],
                "designs" => [
                    [
                        "title" => "M-Back",
                        "src" => "https://drive.google.com/uc?export=download&id=15CjjXnwI1QQ5q6YSsrTXwxCIGqPlBjE_"
                    ]
                ]
            ]
        ],
        "shipping_method" => "stamps_com",
        "comments" => "",
        "order_date" => "2025-06-23T09:03:08",
        "shipstation_order_id" => "98039414",
        "tracking_number" => "9400150206217141521063",
        "label_url" => "https://api.shipstation.com/v2/downloads/14/6sn9odol0ky7omZU_832GQ/label-43410850.png",
        "ags_status" => "Completed",
        "ags_job_id" => "94c08c1da1044ebab724512070f2967b",
        "ags_size" => "13",
        "ags_result" => "https://print.autogangsheet.com/uploads/12/HMFull-15-14_1of1_01945b9d-3eca-4edd-bc8d-124b51a6b2a2.png",
        "status" => "completed"
    ]
];

// Test các trường hợp tracking number khác nhau
$testCases = [
    [
        'name' => 'Tracking number hợp lệ',
        'data' => [
            'id' => 'test-1',
            'tracking_number' => '9400150206217141521063',
            'status' => 'completed'
        ]
    ],
    [
        'name' => 'Tracking number rỗng',
        'data' => [
            'id' => 'test-2',
            'tracking_number' => '',
            'status' => 'completed'
        ]
    ],
    [
        'name' => 'Tracking number null',
        'data' => [
            'id' => 'test-3',
            'tracking_number' => null,
            'status' => 'completed'
        ]
    ],
    [
        'name' => 'Tracking number "No shipment"',
        'data' => [
            'id' => 'test-4',
            'tracking_number' => 'No shipment',
            'status' => 'completed'
        ]
    ],
    [
        'name' => 'Tracking number "null" string',
        'data' => [
            'id' => 'test-5',
            'tracking_number' => 'null',
            'status' => 'completed'
        ]
    ]
];

echo "=== TEST LOGIC XỬ LÝ TRACKING NUMBER DTF (CHỈ GỌI 1 API) ===\n\n";

foreach ($testCases as $testCase) {
    echo "Test: {$testCase['name']}\n";
    echo "Data: " . json_encode($testCase['data'], JSON_PRETTY_PRINT) . "\n";

    $trackingNumber = $testCase['data']['tracking_number'];
    $status = $testCase['data']['status'];

    // Logic kiểm tra tracking number hợp lệ (giống như trong code)
    $isValidTrackingNumber = !empty($trackingNumber) &&
        $trackingNumber !== 'No shipment' &&
        $trackingNumber !== 'null' &&
        $trackingNumber !== '';

    // Logic cập nhật status
    $newStatus = ($status === 'completed') ? 'Shipped' : 'processed';

    echo "Tracking Number: " . ($trackingNumber ?? 'NULL') . "\n";
    echo "Original Status: {$status}\n";
    echo "New Status: {$newStatus}\n";
    echo "Is Valid Tracking: " . ($isValidTrackingNumber ? 'YES' : 'NO') . "\n";
    echo "Action: " . ($isValidTrackingNumber ? 'Cập nhật tracking number và status' : 'Chỉ cập nhật status') . "\n";
    echo "---\n\n";
}

echo "=== KẾT QUẢ XỬ LÝ DỮ LIỆU MẪU ===\n\n";

foreach ($sampleDtfData as $order) {
    echo "Order ID: {$order['id']}\n";
    echo "External ID: {$order['external_id']}\n";
    echo "Tracking Number: {$order['tracking_number']}\n";
    echo "Original Status: {$order['status']}\n";

    $trackingNumber = $order['tracking_number'];
    $status = $order['status'];

    $isValidTrackingNumber = !empty($trackingNumber) &&
        $trackingNumber !== 'No shipment' &&
        $trackingNumber !== 'null' &&
        $trackingNumber !== '';

    $newStatus = ($status === 'completed') ? 'Shipped' : 'processed';

    echo "New Status: {$newStatus}\n";
    echo "Is Valid Tracking: " . ($isValidTrackingNumber ? 'YES' : 'NO') . "\n";
    echo "Action: " . ($isValidTrackingNumber ? 'Cập nhật tracking number và status' : 'Chỉ cập nhật status') . "\n";
    echo "---\n\n";
}

echo "=== TÓM TẮT THAY ĐỔI ===\n\n";
echo "1. Chỉ gọi một API duy nhất: /api/orders?ids=...\n";
echo "2. Lấy cả tracking_number và status từ cùng một response\n";
echo "3. Loại bỏ method getOrdersStatus() không cần thiết\n";
echo "4. Cải thiện hiệu suất và giảm số lượng API calls\n";
