# Hướng Dẫn Tạo Command Từ Đầu

## Tổng Quan

Hướng dẫn này sẽ chỉ cho bạn cách tạo một Laravel Console Command từ đầu và đăng ký nó trong kernel.

## Bước 1: Tạo File Command

### 1.1. Tạo file mới trong `app/Console/Commands/`

```bash
touch app/Console/Commands/YourCommand.php
```

### 1.2. Cấu trúc cơ bản của một Command

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class YourCommand extends Command
{
    // Định nghĩa signature của command
    protected $signature = 'your:command {argument} {--option}';

    // Mô tả command
    protected $description = 'Mô tả command của bạn';

    // Constructor
    public function __construct()
    {
        parent::__construct();
    }

    // Phương thức chính để thực thi
    public function handle()
    {
        // Logic của command
        return 0; // Trả về 0 = thành công
    }
}
```

## Bước 2: Định Nghĩa Signature

### 2.1. Các thành phần của signature:

-   **Command name**: `your:command`
-   **Arguments**: `{argument}` (bắt buộc) hoặc `{argument?}` (tùy chọn)
-   **Options**: `{--option}` hoặc `{--option=default}`

### 2.2. Ví dụ signature phức tạp:

```php
protected $signature = 'email:send
                       {user : ID của user}
                       {--queue : Gửi qua queue}
                       {--subject=default : Tiêu đề email}';
```

## Bước 3: Thực Hiện Logic Trong handle()

### 3.1. Lấy arguments và options:

```php
public function handle()
{
    $user = $this->argument('user');
    $queue = $this->option('queue');
    $subject = $this->option('subject');
}
```

### 3.2. Hiển thị output:

```php
// Thông tin thành công (màu xanh)
$this->info('✅ Thành công!');

// Cảnh báo (màu vàng)
$this->warn('⚠️ Cảnh báo!');

// Lỗi (màu đỏ)
$this->error('❌ Lỗi!');

// Text thường
$this->line('Thông tin thường');
```

### 3.3. Tương tác với user:

```php
// Xác nhận yes/no
if ($this->confirm('Bạn có chắc chắn?')) {
    // User chọn yes
}

// Nhập text
$name = $this->ask('Tên của bạn là gì?');

// Chọn từ danh sách
$type = $this->choice('Chọn loại', ['option1', 'option2']);
```

### 3.4. Progress Bar:

```php
$bar = $this->output->createProgressBar(100);
$bar->start();

for ($i = 0; $i < 100; $i++) {
    // Công việc
    $bar->advance();
}

$bar->finish();
```

### 3.5. Hiển thị bảng:

```php
$this->table(
    ['Cột 1', 'Cột 2'],
    [
        ['Giá trị 1', 'Giá trị 2'],
        ['Giá trị 3', 'Giá trị 4']
    ]
);
```

## Bước 4: Đăng Ký Command Trong Kernel

### 4.1. Mở file `app/Console/Kernel.php`

### 4.2. Thêm command vào mảng `$commands`:

```php
protected $commands = [
    \App\Console\Commands\ExistingCommand::class,
    \App\Console\Commands\YourCommand::class, // ← Thêm dòng này
];
```

## Bước 5: Scheduling Commands (Tùy chọn)

### 5.1. Thêm vào phương thức `schedule()` trong Kernel:

```php
protected function schedule(Schedule $schedule): void
{
    // Chạy hàng ngày lúc 1:00 AM
    $schedule->command('your:command')
        ->dailyAt('01:00')
        ->withoutOverlapping()
        ->runInBackground();

    // Các tùy chọn khác:
    // ->hourly()           // Mỗi giờ
    // ->daily()            // Hàng ngày
    // ->weekly()           // Hàng tuần
    // ->monthly()          // Hàng tháng
    // ->everyMinute()      // Mỗi phút
    // ->everyFiveMinutes() // Mỗi 5 phút
}
```

## Bước 6: Test Command

### 6.1. Chạy command từ terminal:

```bash
# Cú pháp cơ bản
php artisan your:command

# Với arguments và options
php artisan your:command user123 --queue --subject="Hello"

# Xem danh sách tất cả commands
php artisan list

# Xem chi tiết một command
php artisan help your:command
```

## Ví Dụ Hoàn Chỉnh

Dưới đây là ví dụ command đã được tạo:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExampleCommand extends Command
{
    protected $signature = 'example:run {action} {--force}';
    protected $description = 'Command ví dụ';

    public function handle()
    {
        $action = $this->argument('action');
        $force = $this->option('force');

        $this->info("🚀 Đang thực hiện action: {$action}");

        switch ($action) {
            case 'test':
                $this->runTest($force);
                break;
            default:
                $this->error("Action '{$action}' không được hỗ trợ!");
                return 1;
        }

        return 0;
    }

    private function runTest($force)
    {
        if (!$force && !$this->confirm('Chạy test?')) {
            $this->warn('Test đã bị hủy');
            return;
        }

        $this->info('✅ Test hoàn thành!');
    }
}
```

## Lưu Ý Quan Trọng

1. **Return codes**: Luôn trả về `0` nếu thành công, khác `0` nếu lỗi
2. **Exception handling**: Sử dụng try-catch để xử lý lỗi
3. **Logging**: Log các hoạt động quan trọng
4. **Memory**: Cẩn thận với memory khi xử lý dữ liệu lớn
5. **Time limit**: Thiết lập timeout cho các tác vụ dài

## Chạy Command

```bash
# Chạy command ví dụ
php artisan example:run test
php artisan example:run test --force
php artisan example:run cleanup
php artisan example:run status
```

## Cron Job Setup (Cho Production)

```bash
# Mở crontab
crontab -e

# Thêm dòng này (thay đổi đường dẫn project)
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

Chúc bạn tạo command thành công! 🎉
