# FinSafe

FinSafe là ứng dụng quản lý tài chính cá nhân, có xác thực người dùng, TOTP, giao dịch, ngân sách, danh mục và khu vực quản trị. Môi trường development chạy bằng Docker Compose.

## Mục lục

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

## Công nghệ và dịch vụ

| Thành phần | Cấu hình thực tế | Mục đích |
| --- | --- | --- |
| Backend | Laravel `^13.17`, PHP `8.4` FPM | Ứng dụng web |
| Web server | Nginx `1.27-alpine` | Phục vụ app tại host port `8080` |
| Database | MySQL `8.4` | Database trong Docker network (`db:3306`) |
| Frontend | Node `22` (`node:22-bookworm`), Vite `^8`, Tailwind CSS, Alpine.js | HMR/dev server và build assets |
| Database UI | phpMyAdmin `5.2.1-apache` | Quản trị MySQL local tại port `8081` |
| Quality | PHPUnit 12, Laravel Pint, PHPStan/Larastan | Test, format, static analysis |

Compose service names là `app`, `node`, `nginx`, `db`, `phpmyadmin`. Tên containers là `finsafe_app`, `finsafe_node`, `finsafe_nginx`, `finsafe_db`, `finsafe_phpmyadmin`.

## Yêu cầu

Cài trên **máy host**:

- Git;
- Docker Engine/Docker Desktop đang chạy, gồm Docker Compose v2 (`docker compose`);
- GNU Make (khuyến nghị; không có thì dùng các lệnh Compose tương đương).

Không cần cài PHP, Composer, Node.js, npm hoặc MySQL trên host. Các host port `8080`, `8081`, `5175` phải còn trống. MySQL không được publish ra host.

## Khởi chạy nhanh

Chạy tại **máy host**, trong thư mục repository:

```bash
git clone <repository-url> finsafe
cd finsafe
make init
```

`make init` sẽ tạo `.env` từ `.env.example` nếu chưa có, build image PHP, khởi động toàn bộ services, tạo `APP_KEY` nếu thiếu và chạy migrations. Mở app tại [http://localhost:8080](http://localhost:8080).

Vite dev server đã chạy cùng stack để HMR tại [http://localhost:5175](http://localhost:5175). Request trực tiếp `/` trên port Vite có thể trả `404`; đây là bình thường vì Vite không có route root.

> `make init` không chạy seed. Xem phần [Database, migration và seed](#database-migration-và-seed) nếu cần dữ liệu mẫu.

### Không có Make trên host

```bash
cp .env.example .env
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose up -d --build
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose exec app php artisan key:generate --force
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose exec app php artisan migrate --force
```

`HOST_UID`/`HOST_GID` giúp tránh file cache/log thuộc `root` trên Linux/WSL. Nếu host không có `id -u`/`id -g`, đặt hai biến theo UID/GID local hoặc dùng default `1000` trong `compose.yaml` nếu phù hợp.

## Biến môi trường

`.env` là file local và không được commit. Tạo file này bằng:

```bash
cp .env.example .env
```

Cấu hình Docker local quan trọng trong `.env.example`:

```dotenv
APP_URL=http://localhost:8080
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=finsafe
DB_USERNAME=finsafe
```

`DB_HOST=db` và `DB_PORT=3306` là địa chỉ **trong Docker network**, không đổi thành `localhost` cho Laravel container. Password/database chi tiết nằm trong `.env.example` và `compose.yaml`; chỉ đổi cho môi trường local mới trước lần MySQL khởi tạo đầu tiên. Sau khi đổi `.env`:

```bash
make artisan optimize:clear
```

Không commit SMTP, AWS, OAuth, API key hoặc secret thật; đặt chúng trong `.env` local/secret manager.

## Truy cập dịch vụ

| Dịch vụ | URL/kết nối | Ghi chú |
| --- | --- | --- |
| Ứng dụng qua Nginx | [http://localhost:8080](http://localhost:8080) | Điểm truy cập chính |
| phpMyAdmin | [http://localhost:8081](http://localhost:8081) | Chạy mặc định; kết nối DB qua `db:3306` |
| Vite/HMR | [http://localhost:5175](http://localhost:5175) | Vite nội bộ `5173`; browser/HMR `5175` |
| MySQL từ app/phpMyAdmin | `db:3306` | Không expose ra host |

## Database, migration và seed

`make init` đã chạy migration. Khi pull migration mới, chạy tại host:

```bash
make migrate
make artisan migrate:status
```

Lệnh đầu áp dụng migration; lệnh sau chỉ kiểm tra trạng thái.

### Seed dữ liệu (tùy chọn)

`DatabaseSeeder` gọi seeder người dùng quản trị và danh mục mặc định. Chỉ chạy trên database local cho phép dữ liệu mẫu:

```bash
make artisan db:seed
```

Seeder dùng `updateOrCreate`/`firstOrCreate`, nhưng vẫn cần xem `database/seeders/` trước khi chạy. Không coi dữ liệu hoặc credential seed là credential production, và không đưa chúng vào tài liệu public.

### Reset database local (phá hủy dữ liệu)

```bash
make artisan migrate:fresh
# Reset rồi seed
make artisan migrate:fresh --seed
```

Hai lệnh này xóa toàn bộ bảng; chỉ dùng khi chủ động reset database local.

## Lệnh phát triển

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

Service `node` đã tự chạy `npm run dev -- --host 0.0.0.0` và tự `npm ci` nếu `node_modules` thiếu hoặc `package-lock.json` đổi; thông thường không cần chạy `make npm run dev`.

Nếu không dùng Make:

```bash
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose exec app php artisan route:list
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose exec app composer install
HOST_UID=$(id -u) HOST_GID=$(id -g) docker compose exec node npm run build
docker compose logs -f app node nginx db phpmyadmin
```

## Kiểm thử và chất lượng code

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

Theo `phpunit.xml`, test suite dùng SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), không dùng MySQL local. PHP image có extension `sqlite3`. CI hiện chạy Pint, PHPStan, Composer audit, PHPUnit, frontend build và Docker image build/scan.

## Dữ liệu Docker

Compose dùng named volumes:

| Volume | Dữ liệu |
| --- | --- |
| `finsafe_db_data` | MySQL files tại `/var/lib/mysql` |
| `finsafe_node_modules` | npm dependencies của Node service |

`make down` không xóa volumes. Không dùng `docker compose down -v` trừ khi thực sự muốn xóa database local và Node dependencies. Kiểm tra volumes:

```bash
docker volume ls | grep '^finsafe_'
```

## Xử lý lỗi thường gặp

### Host port đã được sử dụng

```bash
ss -ltn '( sport = :8080 or sport = :8081 or sport = :5175 )'
docker ps --format 'table {{.Names}}\t{{.Ports}}'
```

Dừng service xung đột nếu phù hợp, hoặc đổi host mapping trong `compose.yaml`. Nếu đổi port Vite, đồng bộ `hmr.clientPort` trong `vite.config.js`.

### Laravel không kết nối MySQL

```bash
make ps
docker inspect --format '{{.State.Health.Status}}' finsafe_db
docker compose logs --tail=100 db app
```

DB phải `healthy`; `.env` phải dùng `DB_HOST=db` và `DB_PORT=3306`. Sau đổi `.env`, chạy `make artisan optimize:clear`.

### Vite/HMR hoặc dependency Node lỗi

```bash
docker compose logs --tail=100 node
```

Node tự chạy `npm ci` khi lockfile đổi. Nếu volume Node hỏng, chỉ reset Node volume (không đụng database):

```bash
docker compose stop node
docker compose rm -f node
docker volume rm finsafe_node_modules
docker compose up -d node
```

### Sửa Dockerfile nhưng không thấy thay đổi

```bash
make rebuild
docker compose logs --tail=100 app
```

### Lỗi quyền ghi `storage`/`bootstrap/cache` trên Linux/WSL

Dùng Makefile hoặc prefix Compose với `HOST_UID=$(id -u) HOST_GID=$(id -g)`. Service `app` dùng UID/GID host để tránh file bind mount thuộc root.

