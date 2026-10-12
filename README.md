# 1 FinSafe

FinSafe là ứng dụng quản lý tài chính cá nhân, có xác thực người dùng, TOTP, giao dịch, ngân sách, danh mục và khu vực quản trị. Môi trường development chạy bằng Docker Compose.

## 2 Mục lục

- [Công nghệ và dịch vụ](#công-nghệ-và-dịch-vụ)
- [Yêu cầu](#yêu-cầu)
- [Khởi chạy nhanh](#khởi-chạy-nhanh)
- [Biến môi trường](#biến-môi-trường)
- [Truy cập dịch vụ](#truy-cập-dịch-vụ)
- [Database, migration và seed](#database-migration-và-seed)
- [Lệnh phát triển](#lệnh-phát-triển)
- [Kiểm thử và chất lượng code](#kiểm-thử-và-chất-lượng-code)
- [Dữ liệu Docker](#dữ-liệu-docker)
- [Xử lý lỗi thường gặp](#xử-lý-lỗi-thường-gặp)

## 3 Công nghệ và dịch vụ

| Thành phần | Cấu hình thực tế | Mục đích |
| --- | --- | --- |
| Backend | Laravel `^13.17`, PHP `8.4` FPM | Ứng dụng web |
| Web server | Nginx `1.27-alpine` | Phục vụ app tại host port `8080` |
| Database | MySQL `8.4` | Database trong Docker network (`db:3306`) |
| Frontend | Node `22` (`node:22-bookworm`), Vite `^8`, Tailwind CSS, Alpine.js | HMR/dev server và build assets |
| Database UI | phpMyAdmin `5.2.1-apache` | Quản trị MySQL local tại port `8081` |
| Quality | PHPUnit 12, Laravel Pint, PHPStan/Larastan | Test, format, static analysis |

Compose service names là `app`, `node`, `nginx`, `db`, `phpmyadmin`. Tên containers là `finsafe_app`, `finsafe_node`, `finsafe_nginx`, `finsafe_db`, `finsafe_phpmyadmin`.

Không cần cài PHP, Composer, Node.js, npm hoặc MySQL trên host. Các host port `8080`, `8081`, `5175` phải còn trống. MySQL không được publish ra host.

## 4 Cài đặt phần mềm
- Tải Docker desktop và wsl 
- Docker thì tải trên mạng
- WSL thì mở powershell, chạy bằng admin với lệnh `wsl --install`
- Sau khi cài đặt thành công, mở Docker ra và cài đặt như sau
- Setting -> Resource -> WSL Intergration -> Bật Enable integration with my default WSL distro và Ubuntu => restart

## 5 Khởi chạy nhanh

Chạy tại **máy host**, clone code, chuyển sang nhánh đang phát triển và thực hiện khởi động nhanh dự án: Trong trường hợp khi chạy lệnh `make init` lần đầu tiên bị lỗi, thực hiện chạy thêm 1 lần nũa

```bash
git clone https://github.com/Min9701/finsafe.git
cd finsafe
git checkout feature/phase_1/add_bussiness_code
make init
make artisan db:seed
```


## 6 Truy cập dịch vụ
Tài khoản đăng nhập phpMyAdmin
username: finsafe
password: finsafe_password

| Dịch vụ | URL/kết nối | Ghi chú |
| --- | --- | --- |
| Ứng dụng qua Nginx | [http://localhost:8080](http://localhost:8080) | Điểm truy cập chính |
| phpMyAdmin | [http://localhost:8081](http://localhost:8081) | Chạy mặc định; kết nối DB qua `db:3306` |
| Vite/HMR | [http://localhost:5175](http://localhost:5175) | Vite nội bộ `5173`; browser/HMR `5175` |
| MySQL từ app/phpMyAdmin | `db:3306` | Không expose ra host |

## 7 Database, migration và seed
Các lệnh này phục vụ cho migrate và chạy dữ liệu giả lập

```bash
make migrate
make artisan migrate:status
make artisan db:seed
```


## 8 Lệnh phát triển

Tất cả lệnh sau chạy tại **host**, trong thư mục project.

| Lệnh | Mục đích |
| --- | --- |
| `make up` | Khởi động services không build lại image. |
| `make down` | Dừng/xóa containers và network, nhưng giữ named volumes. |
| `make restart` | Chạy `down` rồi `up`; vẫn giữ named volumes. |
| `make rebuild` | Build lại image PHP và recreate stack sau khi đổi `Dockerfile`. |
| `make ps` | Xem service, container và published ports. |
| `make logs` | Theo dõi log mọi service; dừng bằng `Ctrl+C`. |
| `make artisan route:list` | Chạy Artisan trong service `app`. |
| `make artisan optimize:clear` | Xóa Laravel cache sau đổi `.env`/config. |
| `make composer install` | Cài PHP packages theo lockfile trong `app`. |
| `make npm run build` | Build production assets trong service `node`. |

## 9 Kiểm thử và chất lượng code

Chạy tại host; tools thực thi trong `app` container:

```bash
# Laravel/PHPUnit tests
make composer test

# Kiểm tra PHP style, không sửa file
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose exec app vendor/bin/pint --test

# Áp dụng PHP formatting
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose exec app vendor/bin/pint

# Static analysis theo phpstan.neon
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G

# Dependency vulnerability audit
make composer audit
```
