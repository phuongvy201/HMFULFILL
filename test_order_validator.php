<?php

require_once 'vendor/autoload.php';

use App\Services\OrderRowValidator;

// Test OrderRowValidator
$validator = new OrderRowValidator();

// Test data
$testRows = [
    [
        'A' => 'TEST001',
        'E' => 'John',
        'H' => '123 Main St',
        'J' => 'London',
        'K' => 'Westminster',
        'L' => 'SW1A 1AA',
        'M' => 'UK',
        'Q' => 'TEST-SKU-UK',
        'S' => '1',
        'X' => 'Front',
        'Y' => 'https://example.com/mockup.jpg',
        'Z' => 'https://example.com/design.jpg'
    ]
];

$errors = $validator->validateRows($testRows, 'UK');

echo "Validation completed.\n";
echo "Errors found: " . count($errors) . "\n";

if (!empty($errors)) {
    foreach ($errors as $row => $rowErrors) {
        echo "Row $row:\n";
        foreach ($rowErrors as $error) {
            echo "  - $error\n";
        }
    }
} else {
    echo "No validation errors found.\n";
}
