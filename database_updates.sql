-- Create salaries table if it doesn't exist
CREATE TABLE IF NOT EXISTS salaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    basic_salary DECIMAL(15,2) NOT NULL DEFAULT 0,
    actual_salary DECIMAL(15,2) NOT NULL DEFAULT 0,
    allowance DECIMAL(15,2) NOT NULL DEFAULT 0,
    bonus DECIMAL(15,2) NOT NULL DEFAULT 0,
    deductions DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax DECIMAL(15,2) NOT NULL DEFAULT 0,
    effective_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- Create evaluations table if it doesn't exist
CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    evaluation_date DATE NOT NULL,
    rating DECIMAL(3,1) NOT NULL,
    performance_level ENUM('Excellent', 'Good', 'Average', 'Poor') NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- Create evaluation_details table for storing individual criteria scores
CREATE TABLE IF NOT EXISTS evaluation_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id INT NOT NULL,
    criterion VARCHAR(50) NOT NULL,
    score INT NOT NULL,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE
);

-- Insert sample data for salaries
INSERT INTO salaries (employee_id, basic_salary, actual_salary, allowance, bonus, deductions, tax, effective_date)
VALUES 
(1, 10000000, 12000000, 1500000, 500000, 200000, 1200000, '2023-01-01'),
(2, 8000000, 9500000, 1000000, 300000, 150000, 950000, '2023-01-01'),
(3, 15000000, 18000000, 2000000, 1000000, 300000, 1800000, '2023-01-01');

-- Insert sample data for evaluations
INSERT INTO evaluations (employee_id, evaluation_date, rating, performance_level, comments)
VALUES 
(1, '2023-06-30', 8.5, 'Good', 'Consistently performs well and meets expectations.'),
(2, '2023-06-30', 7.2, 'Good', 'Good team player with room for improvement in technical skills.'),
(3, '2023-06-30', 9.3, 'Excellent', 'Outstanding performance in all areas.');

-- Insert sample evaluation details
INSERT INTO evaluation_details (evaluation_id, criterion, score)
VALUES 
(1, 'work_quality', 9),
(1, 'productivity', 8),
(1, 'job_knowledge', 8),
(1, 'reliability', 9),
(1, 'attendance', 8),
(1, 'teamwork', 9),
(2, 'work_quality', 7),
(2, 'productivity', 7),
(2, 'job_knowledge', 6),
(2, 'reliability', 8),
(2, 'attendance', 9),
(2, 'teamwork', 7),
(3, 'work_quality', 10),
(3, 'productivity', 9),
(3, 'job_knowledge', 10),
(3, 'reliability', 9),
(3, 'attendance', 9),
(3, 'teamwork', 9);

-- Create AttendanceDetails table for detailed daily attendance records
CREATE TABLE IF NOT EXISTS AttendanceDetails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attendance_id INT NOT NULL,
    Ngay DATE NOT NULL,
    Trang_thai ENUM('normal', 'absent', 'late', 'overtime') DEFAULT 'normal',
    Gio_tang_ca DECIMAL(4,2) DEFAULT 0,
    Ghi_chu TEXT,
    FOREIGN KEY (attendance_id) REFERENCES Attendance(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance_day (attendance_id, Ngay)
);