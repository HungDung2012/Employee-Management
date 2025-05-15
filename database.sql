-- Create the database

USE CompanyManagement;



-- Create Department table
CREATE TABLE Department (
    id INT PRIMARY KEY AUTO_INCREMENT,
    Ten_phong_ban VARCHAR(50) NOT NULL,
    Truong_phong VARCHAR(50),
    Ngay_nhan_chuc DATE,
    So_nhan_vien INT,
    Luong_trung_binh DECIMAL(12,2)
);

-- Create Employee table
CREATE TABLE Employee (
    id INT PRIMARY KEY AUTO_INCREMENT,
    Ten_nhan_vien VARCHAR(50) NOT NULL,
    Gioi_tinh VARCHAR(10),
    Phong_ban VARCHAR(50),
    Chuc_vu VARCHAR(50),
    Muc_luong DECIMAL(12,2),
    Ngay_bat_dau_hop_dong DATE,
    Ngay_ket_thuc_hop_dong DATE,
    Loai_hop_dong VARCHAR(30),
    Luong_co_ban DECIMAL(12,2),
    Luong_thuc_te DECIMAL(12,2),
    Phu_cap DECIMAL(12,2),
    Luong_thuong DECIMAL(12,2),
    Cac_khoan_tru DECIMAL(12,2),
    Thue DECIMAL(12,2),
    Thuc_lanh DECIMAL(12,2)
);

-- Create Assessment table
CREATE TABLE Assessment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    Ten_nhan_vien VARCHAR(50) NOT NULL,
    Thoi_gian DATE,
    Nguoi_danh_gia VARCHAR(50),
    Diem_danh_gia DECIMAL(3,1),
    Xep_loai VARCHAR(20)
);

CREATE TABLE recruitment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    education_level VARCHAR(10) NOT NULL,
    salary DECIMAL(10, 2),
    status ENUM('Chưa tuyển', 'Đã tuyển') NOT NULL
);

-- Add the Attendance table
CREATE TABLE IF NOT EXISTS Attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    Thoi_gian_cham_cong DATETIME NOT NULL,
    So_ngay_lam_viec INT DEFAULT 0,
    So_ngay_nghi INT DEFAULT 0,
    So_ngay_tre INT DEFAULT 0,
    Gio_lam_them INT DEFAULT 0,
    Thang INT NOT NULL,
    Nam INT NOT NULL,
    Nguoi_cham_cong VARCHAR(100),
    Ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES Employee(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add the AttendanceDetail table
CREATE TABLE IF NOT EXISTS AttendanceDetail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attendance_id INT NOT NULL,
    employee_id INT NOT NULL,
    Ngay DATE NOT NULL,
    Trang_thai ENUM('normal', 'off', 'late', 'overtime') DEFAULT 'normal',
    Gio_tang_ca INT DEFAULT 0,
    Ghi_chu TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attendance_id) REFERENCES Attendance(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES Employee(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance_day (attendance_id, Ngay)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO recruitment (full_name, email, education_level, salary, status) VALUES
('Nguyễn Văn A', 'nguyenvana@example.com', '12/12', 15000000.00, 'Đã tuyển'),
('Trần Thị B', 'tranthib@example.com', '9/12', 8000000.00, 'Chưa tuyển'),
('Lê Văn C', 'levanc@example.com', '12/12', 12000000.00, 'Đã tuyển'),
('Phạm Thị D', 'phamthid@example.com', '9/12', 9000000.00, 'Chưa tuyển'),
('Hoàng Văn E', 'hoangvane@example.com', '12/12', 11000000.00, 'Đã tuyển'),
('Vũ Thị F', 'vuthif@example.com', '12/12', 7500000.00, 'Chưa tuyển'),
('Đặng Văn G', 'dangvang@example.com', '12/12', 13000000.00, 'Đã tuyển'),
('Bùi Thị H', 'buithih@example.com', '12/12', 7000000.00, 'Chưa tuyển'),
('Mai Văn I', 'maivani@example.com', '12/12', 14000000.00, 'Đã tuyển'),
('Lý Thị K', 'lythik@example.com', '12/12', 6500000.00, 'Chưa tuyển'),
('Hồ Văn L', 'hovanl@example.com', '12/12', 16000000.00, 'Đã tuyển'),
('Ngô Thị M', 'ngothim@example.com', '12/12', 6000000.00, 'Chưa tuyển'),
('Đỗ Văn N', 'dovann@example.com', '12/12', 11500000.00, 'Đã tuyển'),
('Trịnh Thị O', 'trinhthio@example.com', '9/12', 5500000.00, 'Chưa tuyển'),
('Chu Văn P', 'chuvanp@example.com', '12/12', 17000000.00, 'Đã tuyển');

-- Insert data into Department table
INSERT INTO Department (Ten_phong_ban, Truong_phong, Ngay_nhan_chuc, So_nhan_vien, Luong_trung_binh) VALUES
('Nhân sự', 'Nguyễn Thị Hương', '2020-05-15', 8, 18500000),
('Kế toán', 'Trần Văn Minh', '2019-11-20', 6, 20500000),
('Kỹ thuật', 'Lê Hoàng Anh', '2021-03-10', 12, 22500000),
('Kinh doanh', 'Phạm Thị Lan', '2018-07-05', 15, 24500000),
('Marketing', 'Vũ Đức Dũng', '2022-01-18', 7, 19500000),
('IT', 'Hoàng Văn Tú', '2020-09-22', 10, 25500000),
('Hành chính', 'Đặng Thị Mai', '2019-04-30', 5, 16500000);

-- Insert data into Employee table
INSERT INTO Employee (Ten_nhan_vien, Gioi_tinh, Phong_ban, Chuc_vu, Muc_luong, Ngay_bat_dau_hop_dong, Ngay_ket_thuc_hop_dong, Loai_hop_dong, Luong_co_ban, Luong_thuc_te, Phu_cap, Luong_thuong, Cac_khoan_tru, Thue, Thuc_lanh) VALUES
('Nguyễn Văn A', 'Nam', 'Nhân sự', 'Nhân viên', 15000000, '2021-01-15', '2023-01-15', 'Có thời hạn', 12000000, 15000000, 2000000, 1000000, 1500000, 750000, 14250000),
('Trần Thị B', 'Nữ', 'Kế toán', 'Kế toán viên', 18000000, '2020-05-20', '2024-05-20', 'Có thời hạn', 15000000, 18000000, 2500000, 1500000, 1800000, 900000, 17100000),
('Lê Văn C', 'Nam', 'Kỹ thuật', 'Kỹ sư', 22000000, '2019-11-10', '2024-05-20', 'Không thời hạn', 20000000, 22000000, 3000000, 2000000, 2200000, 1100000, 20900000),
('Phạm Thị D', 'Nữ', 'Kinh doanh', 'Trưởng nhóm', 25000000, '2018-07-01', '2024-05-20', 'Không thời hạn', 22000000, 25000000, 4000000, 3000000, 2500000, 1250000, 23750000),
('Vũ Đức E', 'Nam', 'Marketing', 'Chuyên viên', 17000000, '2022-02-15', '2024-02-15', 'Có thời hạn', 14000000, 17000000, 2000000, 1200000, 1700000, 850000, 16150000),
('Hoàng Thị F', 'Nữ', 'IT', 'Lập trình viên', 24000000, '2021-03-20', '2023-03-20', 'Có thời hạn', 21000000, 24000000, 3500000, 2500000, 2400000, 1200000, 22800000),
('Đặng Văn G', 'Nam', 'Hành chính', 'Nhân viên', 14000000, '2020-06-10', '2022-12-10', 'Có thời hạn', 11000000, 14000000, 1500000, 800000, 1400000, 700000, 13300000),
('Nguyễn Thị H', 'Nữ', 'Nhân sự', 'Chuyên viên', 19000000, '2019-04-05', '2024-05-20', 'Không thời hạn', 16000000, 19000000, 2800000, 1800000, 1900000, 950000, 18050000),
('Trần Văn I', 'Nam', 'Kế toán', 'Kế toán trưởng', 28000000, '2018-09-15', '2024-05-20', 'Không thời hạn', 25000000, 28000000, 5000000, 4000000, 2800000, 1400000, 26600000),
('Lê Thị K', 'Nữ', 'Kỹ thuật', 'Kỹ sư', 21000000, '2021-07-20', '2023-07-20', 'Có thời hạn', 18000000, 21000000, 2900000, 1900000, 2100000, 1050000, 19950000),
('Phạm Văn L', 'Nam', 'Kinh doanh', 'Nhân viên', 23000000, '2020-08-25', '2022-08-25', 'Có thời hạn', 20000000, 23000000, 3200000, 2200000, 2300000, 1150000, 21850000),
('Vũ Thị M', 'Nữ', 'Marketing', 'Trợ lý', 16000000, '2022-01-10', '2024-01-10', 'Có thời hạn', 13000000, 16000000, 1800000, 1100000, 1600000, 800000, 15200000),
('Hoàng Văn N', 'Nam', 'IT', 'Quản lý dự án', 30000000, '2019-12-05', '2024-05-20', 'Không thời hạn', 27000000, 30000000, 6000000, 5000000, 3000000, 1500000, 28500000),
('Đặng Thị O', 'Nữ', 'Hành chính', 'Thư ký', 15000000, '2021-05-15', '2023-05-15', 'Có thời hạn', 12000000, 15000000, 1700000, 900000, 1500000, 750000, 14250000),
('Nguyễn Văn P', 'Nam', 'Nhân sự', 'Chuyên viên', 20000000, '2020-03-20', '2024-05-20', 'Không thời hạn', 17000000, 20000000, 3000000, 2000000, 2000000, 1000000, 19000000),
('Trần Thị Q', 'Nữ', 'Kế toán', 'Kế toán viên', 17500000, '2021-09-10', '2023-09-10', 'Có thời hạn', 14500000, 17500000, 2400000, 1400000, 1750000, 875000, 16625000),
('Lê Văn R', 'Nam', 'Kỹ thuật', 'Kỹ sư', 22500000, '2019-06-25', '2024-05-20', 'Không thời hạn', 19500000, 22500000, 3100000, 2100000, 2250000, 1125000, 21375000),
('Phạm Thị S', 'Nữ', 'Kinh doanh', 'Nhân viên', 24000000, '2020-11-15', '2022-11-15', 'Có thời hạn', 21000000, 24000000, 3300000, 2300000, 2400000, 1200000, 22800000),
('Vũ Đức T', 'Nam', 'Marketing', 'Chuyên viên', 18500000, '2021-08-05', '2023-08-05', 'Có thời hạn', 15500000, 18500000, 2600000, 1600000, 1850000, 925000, 17575000),
('Hoàng Thị U', 'Nữ', 'IT', 'Lập trình viên', 26000000, '2020-04-20', '2024-05-20', 'Không thời hạn', 23000000, 26000000, 4200000, 3200000, 2600000, 1300000, 24700000);


-- Now insert attendance data only for existing employees
INSERT INTO Attendance (employee_id, Thoi_gian_cham_cong, So_ngay_lam_viec, So_ngay_nghi, So_ngay_tre, Gio_lam_them, Thang, Nam, Nguoi_cham_cong, Ghi_chu) VALUES
(1, '2023-01-31 17:30:00', 22, 2, 1, 8, 1, 2023,  'Nguyễn Thị Hương', 'Nhân viên nghỉ ốm 2 ngày'),
(2, '2023-01-31 17:35:00', 23, 0, 0, 5, 1, 2023,  'Trần Văn Minh', 'Làm thêm cuối tháng'),
(3, '2023-01-31 17:40:00', 21, 1, 3, 12, 1, 2023,  'Lê Hoàng Anh', 'Đi trễ nhiều do kẹt xe'),
(4, '2023-01-31 17:45:00', 23, 0, 0, 15, 1, 2023,  'Phạm Thị Lan', 'Làm thêm nhiều cho dự án'),
(5, '2023-01-31 17:50:00', 20, 3, 2, 4, 1, 2023,  'Vũ Đức Dũng', 'Nghỉ phép 3 ngày'),
(6, '2023-01-31 17:55:00', 22, 1, 0, 10, 1, 2023,  'Hoàng Văn Tú', 'Làm thêm cuối tuần'),
(7, '2023-01-31 18:00:00', 21, 2, 1, 6, 1, 2023,  'Đặng Thị Mai', 'Nghỉ ốm 2 ngày'),
(8, '2023-01-31 18:05:00', 23, 0, 0, 8, 1, 2023,  'Nguyễn Thị Hương', 'Đi làm đầy đủ'),
(9, '2023-01-31 18:10:00', 22, 1, 0, 20, 1, 2023,  'Trần Văn Minh', 'Làm thêm nhiều cho báo cáo tài chính'),
(10, '2023-01-31 18:15:00', 21, 2, 2, 7, 1, 2023,  'Lê Hoàng Anh', 'Nghỉ phép 2 ngày'),
(11, '2023-02-28 17:30:00', 20, 1, 3, 5, 2, 2023,  'Phạm Thị Lan', 'Đi trễ nhiều'),
(12, '2023-02-28 17:35:00', 22, 0, 1, 6, 2, 2023,  'Vũ Đức Dũng', 'Làm thêm ít'),
(13, '2023-02-28 17:40:00', 23, 0, 0, 12, 2, 2023,  'Hoàng Văn Tú', 'Làm thêm nhiều'),
(14, '2023-02-28 17:45:00', 21, 2, 1, 3, 2, 2023,  'Đặng Thị Mai', 'Nghỉ ốm 2 ngày'),
(15, '2023-02-28 17:50:00', 22, 1, 0, 9, 2, 2023,  'Nguyễn Thị Hương', 'Làm thêm cuối tuần'),
(16, '2023-02-28 17:55:00', 23, 0, 0, 4, 2, 2023,  'Trần Văn Minh', 'Đi làm đầy đủ'),
(17, '2023-02-28 18:00:00', 20, 3, 2, 8, 2, 2023,  'Lê Hoàng Anh', 'Nghỉ phép 3 ngày'),
(18, '2023-02-28 18:05:00', 22, 1, 1, 10, 2, 2023,  'Phạm Thị Lan', 'Làm thêm nhiều'),
(19, '2023-02-28 18:10:00', 21, 2, 0, 7, 2, 2023,  'Vũ Đức Dũng', 'Nghỉ ốm 2 ngày'),
(20, '2023-02-28 18:15:00', 23, 0, 0, 15, 2, 2023,  'Hoàng Văn Tú', 'Làm thêm nhiều cho dự án');

-- Insert data into Assessment table
INSERT INTO Assessment (Ten_nhan_vien, Thoi_gian, Nguoi_danh_gia, Diem_danh_gia, Xep_loai) VALUES
('Nguyễn Văn A', '2022-01-10', 'Nguyễn Thị Hương', 8.5, 'Khá'),
('Trần Thị B', '2022-01-12', 'Trần Văn Minh', 9.0, 'Tốt'),
('Lê Văn C', '2022-01-15', 'Lê Hoàng Anh', 9.5, 'Xuất sắc'),
('Phạm Thị D', '2022-01-18', 'Phạm Thị Lan', 8.0, 'Khá'),
('Vũ Đức E', '2022-06-20', 'Vũ Đức Dũng', 7.5, 'Trung bình'),
('Hoàng Thị F', '2022-06-22', 'Hoàng Văn Tú', 9.2, 'Tốt'),
('Đặng Văn G', '2022-06-25', 'Đặng Thị Mai', 7.0, 'Trung bình'),
('Nguyễn Thị H', '2022-01-10', 'Nguyễn Thị Hương', 8.8, 'Tốt'),
('Trần Văn I', '2022-01-12', 'Trần Văn Minh', 9.7, 'Xuất sắc'),
('Lê Thị K', '2022-01-15', 'Lê Hoàng Anh', 8.3, 'Khá'),
('Phạm Văn L', '2022-01-18', 'Phạm Thị Lan', 7.8, 'Khá'),
('Vũ Thị M', '2022-06-20', 'Vũ Đức Dũng', 8.0, 'Khá'),
('Hoàng Văn N', '2022-06-22', 'Hoàng Văn Tú', 9.8, 'Xuất sắc'),
('Đặng Thị O', '2022-06-25', 'Đặng Thị Mai', 7.2, 'Trung bình'),
('Nguyễn Văn P', '2022-01-10', 'Nguyễn Thị Hương', 8.6, 'Khá'),
('Trần Thị Q', '2022-01-12', 'Trần Văn Minh', 7.9, 'Khá'),
('Lê Văn R', '2022-01-15', 'Lê Hoàng Anh', 9.1, 'Tốt'),
('Phạm Thị S', '2022-01-18', 'Phạm Thị Lan', 8.4, 'Khá'),
('Vũ Đức T', '2022-06-20', 'Vũ Đức Dũng', 8.7, 'Tốt'),
('Hoàng Thị U', '2022-06-22', 'Hoàng Văn Tú', 9.4, 'Xuất sắc'),
('Nguyễn Văn A', '2023-01-10', 'Nguyễn Thị Hương', 8.7, 'Tốt'),
('Trần Thị B', '2023-01-12', 'Trần Văn Minh', 9.2, 'Tốt'),
('Lê Văn C', '2023-01-15', 'Lê Hoàng Anh', 9.6, 'Xuất sắc'),
('Phạm Thị D', '2023-01-18', 'Phạm Thị Lan', 8.5, 'Khá'),
('Vũ Đức E', '2023-06-20', 'Vũ Đức Dũng', 8.0, 'Khá');


-- Insert data into AttendanceDetail table (30 records)
INSERT INTO AttendanceDetail (attendance_id, employee_id, Ngay, Trang_thai, Gio_tang_ca, Ghi_chu) VALUES
-- Attendance records for employee 1 (January 2023)
(1, 1, '2023-01-02', 'normal', 1, 'Làm thêm buổi tối'),
(1, 1, '2023-01-03', 'normal', 0, ''),
(1, 1, '2023-01-04', 'normal', 0, ''),
(1, 1, '2023-01-05', 'late', 0, 'Đi trễ 30 phút'),
(1, 1, '2023-01-06', 'normal', 2, 'Làm thêm cuối tuần'),
(1, 1, '2023-01-09', 'off', 0, 'Nghỉ ốm'),
(1, 1, '2023-01-10', 'off', 0, 'Nghỉ ốm'),
(1, 1, '2023-01-11', 'normal', 0, ''),
(1, 1, '2023-01-12', 'normal', 1, 'Làm thêm buổi tối'),

-- Attendance records for employee 2 (January 2023)
(2, 2, '2023-01-02', 'normal', 1, 'Làm thêm ít'),
(2, 2, '2023-01-03', 'normal', 0, ''),
(2, 2, '2023-01-04', 'normal', 0, ''),
(2, 2, '2023-01-05', 'normal', 0, ''),
(2, 2, '2023-01-06', 'normal', 1, 'Làm thêm cuối tuần'),
(2, 2, '2023-01-30', 'normal', 2, 'Làm thêm báo cáo'),
(2, 2, '2023-01-31', 'normal', 1, 'Làm thêm cuối tháng'),

-- Attendance records for employee 3 (January 2023)
(3, 3, '2023-01-02', 'late', 0, 'Đi trễ 45 phút'),
(3, 3, '2023-01-03', 'late', 0, 'Đi trễ 20 phút'),
(3, 3, '2023-01-04', 'normal', 3, 'Làm thêm nhiều'),
(3, 3, '2023-01-05', 'late', 0, 'Đi trễ 15 phút'),
(3, 3, '2023-01-06', 'normal', 4, 'Làm thêm cả ngày'),
(3, 3, '2023-01-16', 'off', 0, 'Nghỉ phép'),

-- Attendance records for employee 4 (January 2023)
(4, 4, '2023-01-02', 'normal', 2, 'Làm thêm dự án'),
(4, 4, '2023-01-03', 'normal', 2, 'Làm thêm dự án'),
(4, 4, '2023-01-04', 'normal', 3, 'Làm thêm nhiều'),
(4, 4, '2023-01-05', 'normal', 2, 'Làm thêm dự án'),
(4, 4, '2023-01-06', 'overtime', 6, 'Làm thêm cả ngày cuối tuần'),

-- Attendance records for employee 11 (February 2023)
(11, 11, '2023-02-01', 'late', 0, 'Đi trễ 25 phút'),
(11, 11, '2023-02-02', 'late', 0, 'Đi trễ 40 phút'),
(11, 11, '2023-02-03', 'normal', 0, ''),
(11, 11, '2023-02-06', 'late', 0, 'Đi trễ 15 phút'),
(11, 11, '2023-02-07', 'normal', 2, 'Làm thêm buổi tối');