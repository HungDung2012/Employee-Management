# Hệ thống Quản lý Nhân viên

Một giải pháp toàn diện trên nền web để quản lý dữ liệu nhân viên, tuyển dụng, chấm công, hợp đồng, lương, và đánh giá hiệu suất trong tổ chức.

## Tổng quan

Hệ thống Quản lý Nhân viên được thiết kế để đơn giản hóa các quy trình nhân sự, giúp dễ dàng theo dõi thông tin nhân viên, giám sát hiệu suất, và quản lý các nhiệm vụ hành chính. Hệ thống được xây dựng bằng PHP và MySQL, cung cấp giao diện thân thiện với người dùng cho nhân viên HR và quản lý.

## Tính năng

- **Bảng điều khiển**: Tổng quan trực quan về thống kê công ty và các chỉ số quan trọng
- **Quản lý Nhân viên**: Thêm, xem, chỉnh sửa, và xóa hồ sơ nhân viên
- **Quản lý Phòng ban**: Tổ chức nhân viên theo phòng ban
- **Tuyển dụng**: Theo dõi ứng viên và tiến trình tuyển dụng
- **Hợp đồng**: Quản lý hợp đồng nhân viên và gia hạn
- **Quản lý Lương**: Tính toán và theo dõi lương, thưởng, và khấu trừ
- **Theo dõi Chấm công**: Ghi lại chấm công nhân viên và giờ làm việc
- **Đánh giá Hiệu suất**: Đánh giá hiệu suất nhân viên và duy trì hồ sơ
- **Xác thực Người dùng**: Hệ thống đăng nhập an toàn với kiểm soát truy cập dựa trên vai trò

## Lưu Ý:
- Chạy file setup_user.php đầu tiên để khởi tạo admin user

## Công nghệ sử dụng

- **Frontend**: HTML, CSS, JavaScript, Chart.js (cho trực quan hóa dữ liệu)
- **Backend**: PHP
- **Cơ sở dữ liệu**: MySQL

## Bắt đầu

### Yêu cầu

- PHP 7.0 trở lên
- MySQL 5.7 trở lên
- Máy chủ web (Apache/Nginx)

### Cài đặt

1. Sao chép kho lưu trữ vào thư mục máy chủ web của bạn
2. Nhập tệp `database.sql` để tạo cấu trúc cơ sở dữ liệu và dữ liệu mẫu
3. Cấu hình kết nối cơ sở dữ liệu trong `includes/db_connect.php`
4. Truy cập hệ thống thông qua trình duyệt web

### Đăng nhập mặc định

- **Tên đăng nhập**: admin
- **Mật khẩu**: admin123

## Cấu trúc Hệ thống

```
Employee-Management/
├── index.php             # Điểm vào chính của ứng dụng
├── homepage.php          # Trang chủ công khai
├── login.php             # Xác thực người dùng
├── logout.php            # Kết thúc phiên
├── setup_user.php        # Thiết lập người dùng ban đầu
├── database.sql          # Cấu trúc cơ sở dữ liệu và dữ liệu mẫu
├── database_updates.sql  # Cập nhật cơ sở dữ liệu
├── pages/                # Các trang ứng dụng chính
│   ├── dashboard.php     # Bảng điều khiển tổng quan
│   ├── employees.php     # Quản lý nhân viên
│   ├── departments.php   # Quản lý phòng ban
│   ├── recruitment.php   # Theo dõi tuyển dụng
│   ├── contracts.php     # Quản lý hợp đồng
│   ├── salary.php        # Quản lý lương
│   ├── attendance.php    # Theo dõi chấm công
│   └── assessment.php    # Đánh giá hiệu suất
├── includes/             # Các thành phần và tiện ích dùng chung
├── css/                  # Tệp định dạng
├── ajax/                 # Xử lý yêu cầu AJAX
└── images/               # Hình ảnh và tài sản hệ thống
```

## Cấu trúc Cơ sở dữ liệu

Hệ thống bao gồm một số bảng liên kết:
- Users - Xác thực hệ thống
- Employee - Thông tin nhân viên cốt lõi
- Department - Cấu trúc tổ chức
- Recruitment - Theo dõi quy trình tuyển dụng
- Attendance/AttendanceDetail - Theo dõi giờ làm việc
- Assessment - Hồ sơ đánh giá hiệu suất

## Ngôn ngữ

Giao diện hệ thống chủ yếu bằng tiếng Việt.

## Lưu ý về Bảo mật

- Thông tin đăng nhập quản trị viên mặc định nên được thay đổi ngay sau khi cài đặt
- Mật khẩu người dùng được lưu trữ an toàn
- Kiểm soát truy cập dựa trên vai trò giới hạn hành động của người dùng dựa trên quyền hạn


