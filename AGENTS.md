# AGENTS.md — Quy tắc phát triển dự án FinSafe

## 1. Mục đích và nguyên tắc chung

Tài liệu này quy định các nguyên tắc bắt buộc khi AI Agent thực hiện đọc, phân tích, viết mới, sửa lỗi, refactor và kiểm thử mã nguồn trong dự án FinSafe.

### Nguyên tắc bắt buộc

- Đọc và hiểu cấu trúc dự án trước khi sửa đổi mã nguồn.
- Tuân thủ kiến trúc MVC kết hợp phân tầng nghiệp vụ hiện có.
- Mọi thay đổi phải phù hợp với quy ước, naming convention và cách triển khai đang được sử dụng trong dự án.
- Ưu tiên giải pháp đơn giản, dễ đọc, dễ bảo trì, dễ kiểm thử và có thể mở rộng.
- Chỉ sửa những phần cần thiết để đáp ứng yêu cầu; không tự ý refactor các phần không liên quan.
- Không tự ý thay đổi nghiệp vụ, cấu trúc database, API contract hoặc hành vi hiện tại nếu chưa có yêu cầu.
- Tái sử dụng code, class, interface, service, repository và component hiện có khi phù hợp.
- Không tạo thêm tầng xử lý, abstraction hoặc dependency nếu không có lý do rõ ràng.
- Không giả định cấu trúc file, tên class, method hoặc quy ước chưa được xác nhận từ mã nguồn thực tế.
- Nếu yêu cầu chưa rõ hoặc có nguy cơ ảnh hưởng đến nghiệp vụ, phải làm rõ trước khi triển khai.

## 2. Công nghệ và môi trường

Sử dụng stack công nghệ sau làm căn cứ khi phát triển dự án:

| Thành phần | Phiên bản/cấu hình |
|---|---|
| Backend | Laravel `^13.17` |
| PHP | PHP `8.4` FPM |
| Web server | Nginx `1.27-alpine` |
| Database | MySQL `8.4` |
| Frontend runtime | Node.js `22` (`node:22-bookworm`) |
| Frontend build | Vite `^8` |
| CSS | Tailwind CSS |
| JavaScript | Alpine.js |
| Database UI | phpMyAdmin `5.2.1-apache` |
| Testing | PHPUnit 12 |
| Code formatting | Laravel Pint |
| Static analysis | PHPStan / Larastan |

### Quy tắc sử dụng công nghệ

- Không tự ý nâng cấp hoặc thay thế phiên bản framework, runtime hay dependency.
- Trước khi thay đổi package, phải kiểm tra `composer.json`, `composer.lock`, `package.json` và lockfile tương ứng.
- Chỉ sử dụng API, cú pháp và thư viện tương thích với phiên bản thực tế của dự án.
- Không đưa thêm framework frontend hoặc thư viện mới nếu chưa thực sự cần thiết.
- Mọi thay đổi về cấu hình phải tương thích với môi trường Docker hiện có.

## 3. Kiến trúc ứng dụng

Dự án sử dụng kiến trúc MVC kết hợp phân tầng nghiệp vụ theo luồng:

**Request → Controller → Service/Interface → Repository → Model**

Luồng này là nguyên tắc tổ chức code mặc định. Khi dự án có quy ước cụ thể khác tại một module, cần kiểm tra và tuân thủ quy ước hiện hữu trước khi áp dụng.

### 3.1. Request

**Trách nhiệm:** Tiếp nhận, kiểm tra và chuẩn hóa dữ liệu đầu vào của request HTTP.

Quy tắc:

- Sử dụng Form Request khi cần validation phức tạp, có thể tái sử dụng hoặc khi phù hợp với cấu trúc hiện tại.
- Tách validation và authorization phù hợp khỏi Controller.
- Khai báo validation rules rõ ràng, đúng kiểu dữ liệu và đúng yêu cầu nghiệp vụ.
- Sử dụng `$request->validated()` hoặc `$request->safe()` để lấy dữ liệu đã được kiểm tra khi phù hợp.
- Không truyền toàn bộ input chưa kiểm tra xuống Service hoặc Repository.
- Không thực hiện truy vấn nghiệp vụ phức tạp trong Request.
- Không xử lý nghiệp vụ hoặc thao tác database trực tiếp trong Request.
- Không đưa các quy tắc validation phụ thuộc vào trạng thái nghiệp vụ phức tạp vào Request nếu chúng thuộc trách nhiệm của Service.

### 3.2. Controller

**Trách nhiệm:** Điều phối request và trả về response HTTP.

Quy tắc:

- Controller phải mỏng, tập trung vào tiếp nhận request, gọi Service và trả response.
- Sử dụng dependency injection để nhận Service hoặc interface cần thiết.
- Không chứa nghiệp vụ phức tạp.
- Không viết truy vấn Eloquent hoặc SQL trực tiếp trong Controller.
- Không thực hiện vòng lặp xử lý nghiệp vụ phức tạp nếu có thể chuyển trách nhiệm đó sang Service.
- Không tự xử lý những logic đã thuộc trách nhiệm của Request, Service hoặc Repository.
- Trả về đúng loại response theo quy ước của module, ví dụ View, Redirect hoặc JSON.
- Không tự ý thay đổi HTTP status code, cấu trúc response hoặc thông báo lỗi đang được các client sử dụng.
- Chỉ gọi những phương thức Service cần thiết; tránh để Controller phụ thuộc vào chi tiết triển khai của Repository.

### 3.3. Service và Service Interface

**Trách nhiệm:** Xử lý nghiệp vụ và điều phối các thao tác cần thiết để hoàn thành một use case.

Quy tắc:

- Đặt logic nghiệp vụ tại Service thay vì Controller hoặc Repository.
- Service chịu trách nhiệm phối hợp một hoặc nhiều Repository khi nghiệp vụ cần dữ liệu từ nhiều nguồn.
- Service có thể kiểm tra các điều kiện nghiệp vụ, tính toán dữ liệu, tổng hợp kết quả và quyết định luồng xử lý.
- Không viết truy vấn database trực tiếp trong Service nếu thao tác đó thuộc trách nhiệm truy xuất dữ liệu của Repository.
- Sử dụng interface khi cần abstraction rõ ràng, dependency inversion, khả năng thay thế implementation hoặc phù hợp với convention hiện có.
- Khi dự án đã có quy ước `Service Interface` và `Service Implementation`, phải tuân thủ quy ước đó.
- Controller nên phụ thuộc vào interface khi kiến trúc hiện tại đã sử dụng interface làm contract.
- Đăng ký binding interface và implementation trong Service Provider hoặc cơ chế phù hợp của Laravel khi cần.
- Không tạo interface chỉ để bao bọc một class mà không mang lại giá trị, trừ khi dự án đã quy định bắt buộc phải có.
- Không để Service phụ thuộc trực tiếp vào Controller, Request HTTP hoặc đối tượng response.
- Không biến Service thành một class chứa quá nhiều nghiệp vụ không liên quan. Tách theo trách nhiệm nghiệp vụ khi thực sự cần thiết.
- Với các thao tác cần tính toàn vẹn dữ liệu, sử dụng transaction tại tầng điều phối nghiệp vụ phù hợp.

### 3.4. Repository

**Trách nhiệm:** Đóng gói việc truy xuất và thao tác dữ liệu thông qua Model, Eloquent hoặc Query Builder.

Quy tắc:

- Repository chịu trách nhiệm truy vấn, lấy, tạo, cập nhật và xóa dữ liệu theo yêu cầu.
- Không chứa quy tắc nghiệp vụ cấp ứng dụng như quyết định quyền lợi, trạng thái xử lý hay điều kiện chuyển trạng thái.
- Không trả về response HTTP hoặc điều hướng người dùng.
- Không phụ thuộc vào Controller.
- Không tự thực hiện validation HTTP.
- Ưu tiên sử dụng Eloquent và Query Builder có parameter binding.
- Chỉ lấy các cột cần thiết khi phù hợp.
- Đặt tên method theo mục đích truy vấn và kết quả trả về.
- Không tạo các method tổng quát khó hiểu như `processData()` hoặc `handleData()` nếu có thể sử dụng tên thể hiện rõ nghiệp vụ truy xuất dữ liệu.
- Các method cần trả về cùng một kiểu dữ liệu phải duy trì contract rõ ràng và nhất quán.
- Không tự ý thay đổi từ `Collection` sang `array`, từ Model sang scalar hoặc ngược lại nếu điều đó ảnh hưởng đến các tầng gọi.
- Khi cần tối ưu truy vấn, phải đảm bảo kết quả vẫn đúng với nghiệp vụ hiện tại.

### 3.5. Model

**Trách nhiệm:** Đại diện cho dữ liệu và quan hệ dữ liệu trong database.

Quy tắc:

- Sử dụng Eloquent Model để định nghĩa table, primary key, casts, relationships và các thuộc tính liên quan đến dữ liệu.
- Khai báo quan hệ bằng các phương thức phù hợp như `hasMany()`, `belongsTo()`, `belongsToMany()` khi đúng với cấu trúc dữ liệu.
- Sử dụng `$fillable` hoặc `$guarded` một cách có chủ đích để kiểm soát mass assignment.
- Khai báo casts cho các kiểu dữ liệu cần thiết.
- Tránh đưa toàn bộ nghiệp vụ ứng dụng vào Model.
- Không gọi trực tiếp Controller hoặc trả response HTTP từ Model.
- Không lạm dụng accessor, mutator, observer hoặc model event để thực hiện các luồng nghiệp vụ phức tạp.
- Trước khi thay đổi relationship, cast, global scope hoặc event, phải kiểm tra ảnh hưởng tới các nơi đang sử dụng Model.
- Không tự ý thay đổi tên cột, khóa chính, relationship hoặc table mapping.

## 4. Quy tắc phụ thuộc giữa các tầng

Luồng phụ thuộc mặc định:

```text
HTTP Request
    |
    v
Form Request / Request Validation
    |
    v
Controller
    |
    v
Service Interface
    |
    v
Service Implementation
    |
    v
Repository Interface (nếu có)
    |
    v
Repository Implementation
    |
    v
Eloquent Model
    |
    v
Database
```

Quy tắc bắt buộc:

- Không bỏ qua tầng để thực hiện công việc thuộc trách nhiệm của tầng khác.
- Controller không gọi Repository trực tiếp để xử lý nghiệp vụ thông thường.
- Controller không truy vấn Model trực tiếp.
- Service không gọi ngược Controller.
- Repository không phụ thuộc vào Service nghiệp vụ cấp ứng dụng.
- Model không gọi Controller, Request HTTP hoặc response.
- Không truyền các đối tượng HTTP không cần thiết qua các tầng nghiệp vụ.
- Các tầng phải giao tiếp thông qua method, contract và kiểu dữ liệu rõ ràng.
- Không tạo vòng phụ thuộc giữa các class hoặc module.
- Nếu một nghiệp vụ hiện tại có cấu trúc khác, cần đánh giá toàn bộ call site trước khi refactor về cấu trúc chuẩn.

## 5. Quy tắc tổ chức code và naming convention

- Trước khi tạo file mới, phải kiểm tra cấu trúc thư mục hiện tại.
- Đặt class đúng namespace và vị trí theo convention của dự án.
- Sử dụng tên class, method và biến mô tả rõ mục đích.
- Tên class sử dụng PascalCase; tên method và biến sử dụng camelCase theo chuẩn PHP.
- Tên interface cần nhất quán với quy ước hiện có, chẳng hạn hậu tố `Interface` nếu dự án đang sử dụng.
- Không tự ý đổi naming convention của module đang hoạt động.
- Tách class theo một trách nhiệm chính.
- Không tạo các thư mục như `DTO`, `Actions`, `Traits`, `Helpers` hoặc tầng mới nếu chưa có nhu cầu rõ ràng.
- Không đặt logic nghiệp vụ vào helper chỉ để giảm số dòng code trong Service.
- Không tạo class chỉ để chuyển tiếp một lời gọi method mà không mang lại giá trị.
- Tái sử dụng constants và enum hiện có thay vì khai báo các giá trị lặp lại.

## 6. Quy tắc PHP và Laravel

- Tuân thủ PSR-12 và convention của Laravel.
- Sử dụng Laravel Pint để định dạng code.
- Khai báo type hint và return type khi phù hợp.
- Ưu tiên dependency injection thay vì tự khởi tạo dependency bên trong class.
- Không sử dụng `static` chỉ vì muốn gọi method thuận tiện hơn.
- Method static không được sử dụng `$this` và không nên phụ thuộc vào trạng thái instance.
- Không dùng static method để thay thế dependency injection khi class cần repository hoặc service.
- Hạn chế side effect không rõ ràng.
- Sử dụng Collection, array và object đúng mục đích; không chuyển đổi dữ liệu tùy tiện.
- Xử lý ngoại lệ có chủ đích, không bắt exception rồi bỏ qua.
- Không dùng `dd()`, `dump()`, `var_dump()` hoặc debug statement trong code hoàn thiện.
- Không để lại code chết, import không dùng hoặc comment sai với hành vi thực tế.
- Không sử dụng API đã deprecated nếu có giải pháp phù hợp hơn với phiên bản hiện tại.

- Tất cả các hàm và method trong PHP phải có PHPDoc đầy đủ, bao gồm cả `public`, `protected`, `private` và các hàm `static`.
- PHPDoc phải mô tả ngắn gọn mục đích và trách nhiệm của hàm/method.
- Khai báo đầy đủ `@param` cho các tham số và `@return` cho giá trị trả về, sử dụng kiểu dữ liệu chính xác.
- Bổ sung `@throws` khi hàm có thể phát sinh exception cần được mô tả rõ ràng.
- Với method trong Interface, phải có PHPDoc mô tả contract và ý nghĩa của method.
- PHPDoc phải chính xác với implementation thực tế; không ghi sai kiểu dữ liệu, hành vi hoặc giá trị trả về.
- Không viết PHPDoc chung chung, dư thừa hoặc chỉ lặp lại tên hàm mà không cung cấp thêm thông tin hữu ích.
- Khi tạo mới hoặc chỉnh sửa hàm/method, phải bổ sung hoặc cập nhật PHPDoc tương ứng.
- Khi rà soát code hiện có, chủ động bổ sung PHPDoc còn thiếu hoặc sửa PHPDoc không chính xác.
- Không sử dụng PHPDoc để thay thế type declaration có thể khai báo trực tiếp trong PHP, ví dụ `string`, `int`, `array`, `Collection` hoặc kiểu trả về của method.

## 7. Quy tắc Database và Eloquent

- Kiểm tra schema, relationship và truy vấn hiện tại trước khi sửa code liên quan đến dữ liệu.
- Hạn chế N+1 bằng eager loading khi phù hợp.
- Dùng `withCount()` khi cần đếm relationship và điều kiện truy vấn đáp ứng đúng nghiệp vụ.
- Dùng `whereIn()` khi cần lọc theo danh sách ID.
- Hiểu rõ kết quả của `get()`, `first()`, `value()`, `pluck()`, `count()` và các hàm aggregate trước khi sử dụng.
- Với `pluck('value_column', 'key_column')`, cần xác định chính xác cột value và cột key.
- Kiểm tra trường hợp key bị trùng, giá trị `null`, kết quả rỗng và kiểu dữ liệu của ID.
- Không giả định Collection có cùng cấu trúc với array hoặc Eloquent Model.
- Tránh truy vấn lặp khi dữ liệu đã được lấy và có thể tái sử dụng.
- Sử dụng transaction khi nhiều thao tác dữ liệu phải thành công hoặc thất bại cùng nhau.
- Chỉ thêm index khi có căn cứ từ truy vấn, quan hệ hoặc khối lượng dữ liệu.
- Thay đổi schema thông qua migration mới khi phù hợp.
- Không sửa migration đã triển khai ở môi trường dùng chung nếu chưa có kế hoạch migration an toàn.
- Không xóa database, volume hoặc dữ liệu thực tế khi chưa được cho phép.

## 8. Quy tắc phát triển giao diện (Frontend UI)

### 8.1. Nguyên tắc chung

- Sử dụng Blade, Tailwind CSS, Alpine.js và Vite theo cấu hình hiện có của dự án.
- Ưu tiên giao diện nhất quán, dễ bảo trì, có khả năng tái sử dụng và responsive trên các kích thước màn hình.
- Trước khi phát triển giao diện mới, phải kiểm tra các layout, component, partial, module và style hiện có để tận dụng lại.
- Không tạo giao diện mới nếu đã có thành phần đáp ứng được yêu cầu.
- Không tự ý thêm thư viện UI, CSS framework hoặc frontend framework mới.
- Không thay đổi cấu hình Tailwind CSS, Vite hoặc quy trình build nếu không thực sự cần thiết.
- Giữ nguyên thiết kế và hành vi hiện tại của các màn hình không thuộc phạm vi yêu cầu.

### 8.2. Quy tắc sử dụng Tailwind CSS

- Ưu tiên sử dụng các utility class của Tailwind CSS để xây dựng layout, spacing, typography, màu sắc, border, responsive và các trạng thái tương tác.
- Không sử dụng CSS inline thông qua thuộc tính `style` trong HTML hoặc Blade để định dạng giao diện.
- Không sử dụng thuộc tính `style` để thay thế các utility class có sẵn của Tailwind CSS.
- Không tạo CSS riêng chỉ để thay thế các utility class Tailwind có thể đáp ứng trực tiếp.
- Sử dụng các breakpoint và utility responsive của Tailwind để đảm bảo giao diện hoạt động tốt trên desktop, tablet và mobile.
- Sử dụng các trạng thái như `hover:`, `focus:`, `disabled:` và các biến thể tương ứng khi cần.
- Ưu tiên các class theo cấu hình và design token hiện có của dự án thay vì tự tạo màu sắc, spacing hoặc kích thước tùy ý.
- Không lạm dụng các giá trị arbitrary như `w-[137px]`, `mt-[23px]` nếu có thể sử dụng giá trị tiêu chuẩn hoặc token đã được định nghĩa.
- Không tạo các chuỗi class Tailwind quá phức tạp, khó đọc hoặc lặp lại ở nhiều màn hình mà không xem xét khả năng tách thành component dùng chung.
- Không ghép class động theo cách khiến Vite/Tailwind không thể phát hiện class khi build. Nếu cần class động, hãy sử dụng mapping các class hoàn chỉnh hoặc giải pháp phù hợp với phiên bản Tailwind đang được cấu hình.

### 8.3. Quy tắc sử dụng CSS và SCSS

- Không sử dụng CSS inline trong Blade, HTML hoặc các component giao diện.
- Mặc định sử dụng Tailwind CSS cho các nhu cầu tạo kiểu thông thường.
- Sử dụng SCSS cho những giao diện phức tạp mà Tailwind CSS không đáp ứng tốt, chẳng hạn:
  - Component có cấu trúc CSS nhiều lớp và nhiều trạng thái.
  - Các hiệu ứng hoặc animation phức tạp.
  - Style có nhiều quan hệ lồng nhau cần tổ chức rõ ràng.
  - Giao diện có nhiều biến thể hoặc quy tắc responsive đặc thù.
  - Các thành phần cần style riêng với phạm vi và cấu trúc rõ ràng.
- SCSS phải được tổ chức trong thư mục asset theo convention hiện tại của dự án và được biên dịch thông qua Vite.
- Không tự ý tạo thêm pipeline build hoặc công cụ biên dịch CSS khác.
- Không sử dụng SCSS chỉ để viết lại những utility class Tailwind đơn giản.
- Hạn chế selector lồng nhau quá sâu, selector phụ thuộc vào cấu trúc DOM không cần thiết và các quy tắc có độ ưu tiên cao khó kiểm soát.
- Tránh sử dụng `!important` trừ khi có lý do kỹ thuật rõ ràng.
- Không định nghĩa lại các style global hoặc design token nếu dự án đã có quy chuẩn tương ứng.
- Nếu một component cần style riêng, ưu tiên giới hạn phạm vi style để tránh ảnh hưởng đến các màn hình khác.

### 8.4. Tái sử dụng giao diện Common và Module

Trước khi tạo giao diện mới, AI Agent phải kiểm tra khả năng tái sử dụng các thành phần hiện có theo hai nhóm:

**Common — Thành phần dùng chung toàn hệ thống**

Bao gồm các thành phần có thể xuất hiện ở nhiều màn hình hoặc nhiều module, ví dụ:

- Header, footer, navigation, sidebar.
- Button, input, select, checkbox, radio và form control.
- Modal, dropdown, tooltip, alert và notification.
- Pagination, breadcrumb, loading indicator và empty state.
- Card, badge, table và các thành phần hiển thị dữ liệu phổ biến.
- Layout, typography và các thành phần giao diện dùng chung khác.

Quy tắc:

- Ưu tiên sử dụng Common component đã tồn tại.
- Các thành phần dùng chung phải có giao diện, hành vi và cách sử dụng nhất quán.
- Khi cần bổ sung tính năng, ưu tiên mở rộng component hiện có theo hướng tương thích ngược.
- Không tạo nhiều component có chức năng giống nhau chỉ vì được sử dụng ở các màn hình khác nhau.
- Chỉ đưa component vào Common khi nó thực sự dùng chung hoặc có khả năng tái sử dụng rõ ràng, tránh tạo các component quá tổng quát.

**Module — Thành phần thuộc một chức năng hoặc nghiệp vụ cụ thể**

Bao gồm các thành phần được sử dụng trong một module hoặc nhóm màn hình liên quan, ví dụ:

- Bộ lọc dữ liệu của một module.
- Form tạo mới hoặc chỉnh sửa một loại dữ liệu nghiệp vụ.
- Bảng dữ liệu và các thao tác đặc thù của module.
- Widget, dashboard card hoặc phần hiển thị có logic riêng.
- Các nhóm giao diện có cùng cấu trúc trong một chức năng.

Quy tắc:

- Tái sử dụng component trong phạm vi module khi có nhiều màn hình cùng sử dụng.
- Không đưa các thành phần mang tính đặc thù nghiệp vụ vào Common chỉ để giảm số lượng file.
- Nếu một component trong Module phát sinh nhu cầu sử dụng ở nhiều module khác, hãy đánh giá khả năng tổng quát hóa trước khi chuyển sang Common.
- Khi chuyển component từ Module sang Common, phải kiểm tra các nơi sử dụng và đảm bảo không làm thay đổi hành vi hiện tại.
- Không sao chép toàn bộ markup và style sang màn hình mới nếu có thể tái sử dụng component hoặc partial hiện có.

### 8.5. Quy tắc tạo và tổ chức component

- Tuân thủ cấu trúc thư mục Blade, component và asset hiện có của dự án.
- Ưu tiên sử dụng Blade component cho những phần giao diện có thể tái sử dụng và cần giao diện đầu vào rõ ràng.
- Sử dụng Blade partial khi chỉ cần tách một phần template để dùng lại hoặc cải thiện khả năng đọc.
- Không tạo component mới chỉ để bọc một vài dòng markup nếu việc tách không mang lại giá trị rõ ràng.
- Định nghĩa rõ các props, attributes và dữ liệu đầu vào của component.
- Hạn chế để component tự truy vấn database hoặc phụ thuộc trực tiếp vào nghiệp vụ backend.
- Không đặt logic nghiệp vụ phức tạp trong Blade component.
- Ưu tiên truyền dữ liệu cần thiết từ tầng xử lý phù hợp.
- Đặt tên component thể hiện đúng chức năng và nhất quán với quy ước của dự án.
- Giữ cho markup dễ đọc, cấu trúc HTML hợp lý và không lặp lại các đoạn template dài.

### 8.6. Quy tắc JavaScript và Alpine.js

- Sử dụng Alpine.js cho các tương tác giao diện nhẹ như dropdown, modal, tab, toggle và trạng thái hiển thị.
- Tái sử dụng logic tương tác khi các component có hành vi giống nhau.
- Không đưa nghiệp vụ backend hoặc logic bảo mật vào JavaScript phía client.
- Không sử dụng JavaScript để thay thế validation bắt buộc ở server.
- Tránh viết logic tương tác quá phức tạp trực tiếp trong thuộc tính HTML.
- Khi logic Alpine.js trở nên phức tạp hoặc được sử dụng ở nhiều nơi, hãy cân nhắc tách thành module JavaScript theo cấu trúc hiện có.
- Không thêm thư viện JavaScript mới nếu Alpine.js và các công cụ hiện tại đã đáp ứng được yêu cầu.
- Đảm bảo các tương tác hoạt động chính xác với bàn phím, trạng thái focus và các trạng thái loading hoặc disabled khi phù hợp.

### 8.7. Responsive và khả năng tiếp cận

- Phát triển giao diện theo hướng responsive, không chỉ tối ưu cho một kích thước màn hình.
- Ưu tiên cách bố trí linh hoạt thay vì cố định kích thước khi không cần thiết.
- Đảm bảo form, bảng, modal và navigation có thể sử dụng được trên màn hình nhỏ.
- Sử dụng đúng semantic HTML cho button, link, form và các phần tử nội dung.
- Các input cần có label phù hợp.
- Không chỉ sử dụng màu sắc để thể hiện trạng thái hoặc lỗi.
- Đảm bảo trạng thái focus có thể nhận biết khi điều hướng bằng bàn phím.
- Duy trì độ tương phản và khả năng đọc của văn bản.

### 8.8. Quy trình phát triển giao diện

Khi nhận task liên quan đến UI, AI Agent phải thực hiện:

1. Kiểm tra layout, Blade component, partial, Common, Module, Tailwind class và SCSS hiện có.
2. Xác định thành phần nào có thể tái sử dụng và thành phần nào thực sự cần tạo mới.
3. Xác định giải pháp tạo kiểu: ưu tiên Tailwind CSS; sử dụng SCSS khi độ phức tạp thực sự cần thiết.
4. Triển khai theo cấu trúc và naming convention hiện tại.
5. Kiểm tra giao diện trên các kích thước màn hình phù hợp.
6. Kiểm tra các trạng thái tương tác như hover, focus, loading, disabled, empty state và validation error khi liên quan.
7. Chạy frontend build hoặc các lệnh kiểm tra phù hợp nếu môi trường cho phép.
8. Rà soát các thay đổi để phát hiện CSS inline, style trùng lặp, component trùng chức năng hoặc ảnh hưởng ngoài phạm vi.

### 8.9. Tiêu chí hoàn thành UI

Một task giao diện chỉ được xem là hoàn thành khi:

- Sử dụng Tailwind CSS theo quy tắc dự án.
- Không có CSS inline dùng để định dạng giao diện.
- SCSS chỉ được sử dụng khi phù hợp với độ phức tạp của giao diện.
- Đã kiểm tra khả năng tái sử dụng Common và Module.
- Không tạo component hoặc style trùng lặp không cần thiết.
- Không làm hỏng giao diện hoặc hành vi của các màn hình liên quan.
- Đảm bảo responsive và khả năng sử dụng cơ bản.
- Các bước build hoặc kiểm tra đã thực hiện được báo cáo chính xác.

## 9. Quy tắc Docker và môi trường

- Docker Compose là môi trường phát triển chuẩn của dự án.
- Tuân thủ quy tắc đặt tên resource theo tiền tố `finsafe_*` khi cấu hình hỗ trợ.
- Không tự ý thay đổi tên service, container, network, volume hoặc port đang được sử dụng.
- Ứng dụng kết nối MySQL qua hostname `db` và port nội bộ `3306`.
- Web application được truy cập qua host port `8080`.
- phpMyAdmin phục vụ quản trị database local qua host port `8081`.
- Không sử dụng `localhost:3306` để kết nối MySQL từ container ứng dụng.
- Không công khai database port ra bên ngoài nếu không có yêu cầu.
- Không xóa named volume hoặc sử dụng lệnh Docker có khả năng xóa dữ liệu nếu chưa được cho phép.
- Trước khi sửa lỗi Docker, kiểm tra service status, logs, network, volume mount, permission và cấu hình liên quan.
- Không thay đổi UID/GID, ownership hoặc quyền thư mục một cách tùy tiện.
- Không giả định rằng lệnh chạy thành công trên host sẽ chạy thành công bên trong container.
- Ưu tiên xác minh bằng đúng môi trường Docker mà dự án đang sử dụng.

## 10. Bảo mật

- Không commit mật khẩu, API key, access token hoặc thông tin xác thực.
- Sử dụng biến môi trường và Laravel configuration cho cấu hình theo môi trường.
- Không vô hiệu hóa middleware, CSRF protection, authentication hoặc authorization để né lỗi.
- Kiểm tra quyền truy cập cho các thao tác dữ liệu được bảo vệ.
- Sử dụng parameter binding hoặc Eloquent thay vì nối chuỗi SQL với dữ liệu đầu vào.
- Không trả stack trace, thông tin database hoặc dữ liệu nhạy cảm ra response production.
- Không ghi dữ liệu nhạy cảm vào log.
- Không sử dụng dữ liệu request chưa được kiểm tra để thực hiện mass assignment.
- Không chạy lệnh phá hủy dữ liệu, reset database hoặc xóa volume nếu chưa được cho phép.

## 11. Kiểm thử và chất lượng code

- Khi thay đổi nghiệp vụ, cần bổ sung hoặc cập nhật test phù hợp.
- Sử dụng PHPUnit 12 và convention test hiện có.
- Ưu tiên kiểm thử hành vi thực tế thay vì phụ thuộc quá chặt vào chi tiết triển khai nội bộ.
- Bao phủ các trường hợp thành công, dữ liệu rỗng, dữ liệu không hợp lệ, ranh giới và lỗi nghiệp vụ liên quan.
- Khi thay đổi Service, cần kiểm tra các trường hợp nghiệp vụ và tương tác với Repository.
- Khi thay đổi Repository, cần kiểm tra điều kiện truy vấn, kiểu dữ liệu trả về và kết quả thực tế.
- Khi thay đổi Controller hoặc Request, cần kiểm tra validation, authorization và response.
- Chạy Laravel Pint đối với các file PHP đã thay đổi hoặc theo command chuẩn của dự án.
- Chạy PHPStan/Larastan khi cấu hình cho phép.
- Chạy test liên quan trước; chạy toàn bộ test khi phạm vi thay đổi hoặc điều kiện môi trường yêu cầu.
- Chạy frontend build khi thay đổi asset hoặc dependency frontend.
- Không khẳng định test hoặc build thành công nếu chưa thực sự chạy và xác nhận kết quả.
- Nếu không thể chạy kiểm thử, phải nêu rõ nguyên nhân và các bước chưa được xác minh.

## 12. Quy trình thực hiện task

Mỗi task phải tuân thủ các bước sau:

### Bước 1: Phân tích yêu cầu

- Xác định vấn đề, hành vi mong muốn và điều kiện chấp nhận.
- Xác định các tầng có thể bị ảnh hưởng.
- Làm rõ các điểm chưa đủ thông tin nếu có nguy cơ thay đổi sai nghiệp vụ.

### Bước 2: Khảo sát code hiện tại

- Đọc các file liên quan.
- Kiểm tra route, Request, Controller, Service, Interface, Repository và Model theo luồng thực tế.
- Tìm các nơi đang gọi method sẽ thay đổi.
- Kiểm tra test, migration và cấu hình liên quan khi cần thiết.
- Không chỉ dựa vào tên file hoặc giả định về cấu trúc.

### Bước 3: Lập phương án

- Xác định nguyên nhân gốc rễ.
- Chọn giải pháp ít thay đổi nhất nhưng vẫn đáp ứng yêu cầu.
- Xác định các file cần sửa và phạm vi kiểm thử.
- Đánh giá rủi ro về dữ liệu, hiệu năng, bảo mật và tính tương thích.

### Bước 4: Triển khai

- Tuân thủ đúng trách nhiệm từng tầng.
- Tái sử dụng các implementation hiện có.
- Không đưa nghiệp vụ vào sai tầng.
- Không thay đổi những phần không liên quan.
- Không thêm dependency hoặc cấu trúc mới khi chưa cần thiết.

### Bước 5: Xác minh

- Chạy test liên quan.
- Kiểm tra format và static analysis khi phù hợp.
- Kiểm tra frontend build hoặc Docker nếu task liên quan.
- Đọc lại diff để phát hiện thay đổi ngoài phạm vi.
- Xác nhận không làm thay đổi hành vi hiện có ngoài yêu cầu.

### Bước 6: Báo cáo

Kết thúc task, cần báo cáo:

1. Những gì đã thay đổi.
2. Danh sách file đã sửa và trách nhiệm của từng file.
3. Nguyên nhân và cách giải quyết nếu là task sửa lỗi.
4. Các lệnh kiểm tra đã chạy và kết quả thực tế.
5. Những vấn đề chưa được xác minh hoặc rủi ro còn lại.

## 13. Những hành vi AI Agent không được tự ý thực hiện

- Không tự ý thay đổi kiến trúc toàn dự án.
- Không bỏ qua Service hoặc Repository để viết truy vấn trực tiếp trong Controller.
- Không đặt nghiệp vụ vào Repository hoặc Model chỉ để giảm số file.
- Không tạo interface, service hoặc repository mới nếu không cần thiết hoặc trái convention.
- Không tự ý nâng cấp Laravel, PHP, Node.js hoặc các dependency.
- Không sửa các file không liên quan đến task.
- Không xóa test chỉ để làm cho bộ test vượt qua.
- Không thay đổi test để hợp thức hóa một implementation sai yêu cầu.
- Không che giấu lỗi bằng cách bắt exception rồi trả về kết quả mặc định.
- Không tự ý chạy lệnh có khả năng mất dữ liệu.
- Không tuyên bố đã hoàn thành nếu chưa thực hiện các bước xác minh phù hợp.

## 14. Thứ tự ưu tiên khi có xung đột

Khi các quy tắc mâu thuẫn, áp dụng thứ tự sau:

1. Yêu cầu cụ thể và tiêu chí chấp nhận của người dùng.
2. Bảo mật, tính toàn vẹn dữ liệu và an toàn môi trường.
3. Kiến trúc và convention thực tế của dự án.
4. Các quy tắc trong tài liệu này.
5. Best practice chung của Laravel.

**Nguyên tắc cuối cùng:** Mọi thay đổi phải phù hợp với kiến trúc thực tế của FinSafe, giữ đúng luồng nghiệp vụ và có thể kiểm chứng. Không tự suy diễn yêu cầu hoặc áp dụng best practice một cách máy móc khi điều đó làm sai convention của dự án.

## Quy tắc làm việc dành cho AI Agent

### 1. Phạm vi kiểm tra
- Đọc và tuân thủ các quy tắc trong `AGENTS.md` trước khi thực hiện công việc.
- Xác định phạm vi nhiệm vụ trước khi đọc hoặc chỉnh sửa source code.
- Ưu tiên kiểm tra các file được chỉ định và những file có liên quan trực tiếp đến luồng xử lý cần thay đổi.
- Không mặc định rà soát toàn bộ dự án đối với các nhiệm vụ có phạm vi nhỏ.
- Chỉ mở rộng phạm vi khi cần truy vết dependency, kiểm tra nơi sử dụng hoặc xác minh ảnh hưởng của thay đổi.

### 2. Tái sử dụng kết quả đã có
- Tận dụng kết quả phân tích, báo cáo, Git diff và các thay đổi đã được xác minh trong phiên làm việc hiện tại.
- Không thực hiện lại các bước kiểm tra đã hoàn thành nếu không có thay đổi mới hoặc lý do cụ thể.
- Không đọc lại toàn bộ file khi chỉ cần kiểm tra một phần liên quan, nhưng phải đọc đủ ngữ cảnh để hiểu đúng logic.
- Không giả định kết quả audit trước đây vẫn chính xác nếu source code đã thay đổi hoặc thông tin chưa được xác minh.

### 3. Nguyên tắc thực hiện
- Ưu tiên thay đổi nhỏ, đúng phạm vi và nhất quán với kiến trúc hiện tại.
- Không refactor code đã đạt yêu cầu nếu không cần thiết cho nhiệm vụ.
- Không thay đổi nghiệp vụ, API contract, schema database hoặc dependency ngoài phạm vi được yêu cầu.
- Với nhiệm vụ lớn, chia thành các nhóm công việc theo module hoặc chức năng để dễ kiểm soát và xác minh.
- Nếu phát hiện vấn đề ngoài phạm vi, ghi nhận riêng thay vì tự động mở rộng công việc.

### 4. Kiểm tra sau thay đổi
- Sử dụng công cụ tự động của dự án cho các kiểm tra có thể tự động hóa, như formatting, static analysis và testing.
- Ưu tiên kiểm tra các file đã thay đổi và các luồng bị ảnh hưởng trực tiếp.
- Chạy kiểm tra rộng hơn khi thay đổi ảnh hưởng nhiều module, kiến trúc chung hoặc khi cần xác minh tính tương thích.
- Review Git diff để phát hiện thay đổi ngoài ý muốn, code dư thừa và lỗi phát sinh.
- Chỉ báo cáo những kiểm tra thực sự đã chạy và kết quả thực tế.

### 5. Báo cáo hoàn thành
- Tóm tắt các vấn đề đã xử lý và những file đã thay đổi.
- Nêu rõ các lệnh kiểm tra đã chạy và kết quả.
- Phân biệt lỗi đã sửa, lỗi chưa xử lý và vấn đề cần quyết định thêm.
- Không tuyên bố đã rà soát toàn bộ dự án nếu chỉ kiểm tra một phạm vi giới hạn.