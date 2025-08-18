<?php

/**
 * Script kiểm tra server timeout settings
 * Chạy: php check_server_timeout.php
 */

echo "🔍 Kiểm tra Server Timeout Settings\n";
echo "==================================\n\n";

// 1. Kiểm tra PHP settings
echo "1️⃣ PHP Settings:\n";
$phpSettings = [
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_input_time' => ini_get('max_input_time'),
    'default_socket_timeout' => ini_get('default_socket_timeout'),
    'max_input_vars' => ini_get('max_input_vars'),
    'file_uploads' => ini_get('file_uploads'),
    'max_file_uploads' => ini_get('max_file_uploads'),
];

foreach ($phpSettings as $key => $value) {
    $status = '✅';
    if ($key === 'max_execution_time' && $value < 300) $status = '⚠️';
    if ($key === 'memory_limit' && $value < '512M') $status = '⚠️';
    if ($key === 'upload_max_filesize' && $value < '100M') $status = '⚠️';
    if ($key === 'post_max_size' && $value < '100M') $status = '⚠️';
    
    echo "  {$status} {$key}: {$value}\n";
}

// 2. Kiểm tra server software
echo "\n2️⃣ Server Information:\n";
echo "  🌐 Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "  📋 PHP Version: " . PHP_VERSION . "\n";
echo "  🔧 SAPI: " . php_sapi_name() . "\n";

// 3. Kiểm tra .htaccess
echo "\n3️⃣ .htaccess Check:\n";
$htaccessFile = __DIR__ . '/.htaccess';
if (file_exists($htaccessFile)) {
    echo "  ✅ .htaccess exists\n";
    $content = file_get_contents($htaccessFile);
    
    $timeoutSettings = [
        'max_execution_time',
        'memory_limit',
        'upload_max_filesize',
        'post_max_size',
        'max_input_time'
    ];
    
    foreach ($timeoutSettings as $setting) {
        if (strpos($content, $setting) !== false) {
            echo "  ✅ {$setting} configured in .htaccess\n";
        } else {
            echo "  ⚠️  {$setting} not found in .htaccess\n";
        }
    }
} else {
    echo "  ❌ .htaccess not found\n";
}

// 4. Kiểm tra nginx config (nếu có)
echo "\n4️⃣ Nginx Check:\n";
$nginxConf = '/etc/nginx/nginx.conf';
if (file_exists($nginxConf)) {
    echo "  ✅ Nginx config found\n";
    $content = file_get_contents($nginxConf);
    
    $nginxSettings = [
        'client_max_body_size',
        'proxy_read_timeout',
        'proxy_connect_timeout',
        'proxy_send_timeout'
    ];
    
    foreach ($nginxSettings as $setting) {
        if (strpos($content, $setting) !== false) {
            echo "  ✅ {$setting} configured in nginx\n";
        } else {
            echo "  ⚠️  {$setting} not found in nginx config\n";
        }
    }
} else {
    echo "  ℹ️  Nginx config not found (may be using Apache)\n";
}

// 5. Kiểm tra Apache config (nếu có)
echo "\n5️⃣ Apache Check:\n";
$apacheConf = '/etc/httpd/conf/httpd.conf';
if (file_exists($apacheConf)) {
    echo "  ✅ Apache config found\n";
    $content = file_get_contents($apacheConf);
    
    $apacheSettings = [
        'Timeout',
        'KeepAliveTimeout',
        'MaxKeepAliveRequests'
    ];
    
    foreach ($apacheSettings as $setting) {
        if (strpos($content, $setting) !== false) {
            echo "  ✅ {$setting} configured in Apache\n";
        } else {
            echo "  ⚠️  {$setting} not found in Apache config\n";
        }
    }
} else {
    echo "  ℹ️  Apache config not found (may be using Nginx)\n";
}

// 6. Kiểm tra system limits
echo "\n6️⃣ System Limits:\n";
if (function_exists('posix_getrlimit')) {
    $limits = posix_getrlimit();
    foreach ($limits as $name => $limit) {
        if (strpos($name, 'time') !== false || strpos($name, 'size') !== false) {
            echo "  📊 {$name}: {$limit[0]} / {$limit[1]}\n";
        }
    }
} else {
    echo "  ℹ️  posix_getrlimit not available\n";
}

// 7. Recommendations
echo "\n💡 Recommendations:\n";

if (ini_get('max_execution_time') < 300) {
    echo "  🔧 Add to .htaccess:\n";
    echo "     php_value max_execution_time 300\n";
}

if (ini_get('memory_limit') < '512M') {
    echo "  🔧 Add to .htaccess:\n";
    echo "     php_value memory_limit 512M\n";
}

if (ini_get('upload_max_filesize') < '100M') {
    echo "  🔧 Add to .htaccess:\n";
    echo "     php_value upload_max_filesize 100M\n";
    echo "     php_value post_max_size 100M\n";
}

echo "\n🔧 For Nginx (if using):\n";
echo "  client_max_body_size 100M;\n";
echo "  proxy_read_timeout 300s;\n";
echo "  proxy_connect_timeout 60s;\n";

echo "\n🔧 For Apache (if using):\n";
echo "  Timeout 300\n";
echo "  KeepAliveTimeout 60\n";

echo "\n✅ Check completed!\n";



