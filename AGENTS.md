<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

## Quy tắc phát triển giao diện

Áp dụng cho mọi thay đổi frontend trong dự án:

- **Ưu tiên Tailwind CSS:** Dùng các utility class của Tailwind CSS để xây dựng giao diện và định kiểu.
- **Không dùng CSS inline:** Không sử dụng thuộc tính `style` trực tiếp trong HTML, Blade, JSX/TSX, hoặc các template khác.
- **SCSS cho kiểu dáng phức tạp:** Khi cần CSS phức tạp mà Tailwind không thể biểu đạt rõ ràng hoặc phù hợp, viết trong tệp `.scss` của dự án; không thêm CSS inline.
- **Tái sử dụng giao diện:** Tách các phần giao diện, logic, hoặc kiểu dáng được sử dụng chung thành component/module chuyên biệt. Tránh sao chép markup hay style giữa các màn hình.
