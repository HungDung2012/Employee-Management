<?php
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

try {
    $conn->beginTransaction();

    // Check if attendance already exists for this employee and month

    $stmt = $conn->prepare("SELECT id FROM Attendance 
                           WHERE employee_id = :employee_id 
                           AND Thang = :month 
                           AND Nam = :year");
    $stmt->bindParam(':employee_id', $data['employee_id'], PDO::PARAM_INT);
    $stmt->bindParam(':month', $data['month'], PDO::PARAM_INT);
    $stmt->bindParam(':year', $data['year'], PDO::PARAM_INT);
    $stmt->execute();
    // Gán giá trị từ mảng $data (nhận từ JSON) vào các tham số trong câu SQL bằng bindParam.
    //Giúp tránh lỗi SQL injection và xác định kiểu dữ liệu rõ ràng (PDO::PARAM_INT).
    
    $existingId = $stmt->fetchColumn();

    // Create date for the first day of the month
    $attendanceDate = sprintf('%04d-%02d-01', $data['year'], $data['month']);

    if ($existingId) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE Attendance SET 
                               So_ngay_lam_viec = :working_days,
                               So_ngay_nghi = :off_days,
                               So_ngay_tre = :late_days,
                               Gio_lam_them = :overtime_hours,
                               Nguoi_cham_cong = :created_by,
                               Ghi_chu = :notes
                               WHERE id = :id");
        $stmt->bindParam(':working_days', $data['working_days'], PDO::PARAM_INT);
        $stmt->bindParam(':off_days', $data['off_days'], PDO::PARAM_INT);
        $stmt->bindParam(':late_days', $data['late_days'], PDO::PARAM_INT);
        $stmt->bindParam(':overtime_hours', $data['overtime_hours'], PDO::PARAM_INT);
        $stmt->bindParam(':created_by', $_SESSION['Ten_dang_nhap'], PDO::PARAM_STR);
        $stmt->bindValue(':notes', '', PDO::PARAM_STR);
        $stmt->bindParam(':id', $existingId, PDO::PARAM_INT);
        $stmt->execute();

        $attendanceId = $existingId;

        // Delete existing details
        $stmt = $conn->prepare("DELETE FROM AttendanceDetail WHERE attendance_id = :attendance_id");
        $stmt->bindParam(':attendance_id', $attendanceId, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO Attendance 
                               (employee_id, Thoi_gian_cham_cong, So_ngay_lam_viec, So_ngay_nghi, 
                                So_ngay_tre, Gio_lam_them, Thang, Nam, Nguoi_cham_cong, Ghi_chu) 
                               VALUES (:employee_id, :attendance_date, :working_days, :off_days, 
                                      :late_days, :overtime_hours, :month, :year, :created_by, :notes)");
        $stmt->bindParam(':employee_id', $data['employee_id'], PDO::PARAM_INT);
        $stmt->bindParam(':attendance_date', $attendanceDate, PDO::PARAM_STR);
        $stmt->bindParam(':working_days', $data['working_days'], PDO::PARAM_INT);
        $stmt->bindParam(':off_days', $data['off_days'], PDO::PARAM_INT);
        $stmt->bindParam(':late_days', $data['late_days'], PDO::PARAM_INT);
        $stmt->bindParam(':overtime_hours', $data['overtime_hours'], PDO::PARAM_INT);
        $stmt->bindParam(':month', $data['month'], PDO::PARAM_INT);
        $stmt->bindParam(':year', $data['year'], PDO::PARAM_INT);
        $stmt->bindParam(':created_by', $_SESSION['Ten_dang_nhap'], PDO::PARAM_STR);
        $stmt->bindValue(':notes', '', PDO::PARAM_STR);
        $stmt->execute();

        $attendanceId = $conn->lastInsertId();
    }

    // Insert attendance details for each day using AttendanceDetail table
    if (isset($data['attendance_details']) && is_array($data['attendance_details'])) {
        $stmt = $conn->prepare("INSERT INTO AttendanceDetail 
                               (attendance_id, employee_id, Ngay, Trang_thai, Gio_tang_ca, Ghi_chu) 
                               VALUES (:attendance_id, :employee_id, :date, :status, :overtime, :notes)");

        foreach ($data['attendance_details'] as $day => $details) {
            $dayDate = sprintf('%04d-%02d-%02d', $data['year'], $data['month'], $day);
            
            // Map status from frontend to database
            $status = 'normal';
            switch ($details['status']) {
                case 'off':
                    $status = 'absent';
                    break;
                case 'late':
                    $status = 'late';
                    break;
                case 'normal':
                    $status = $details['overtime'] > 0 ? 'overtime' : 'normal';
                    break;
            }

            $stmt->bindParam(':attendance_id', $attendanceId, PDO::PARAM_INT);
            $stmt->bindParam(':employee_id', $data['employee_id'], PDO::PARAM_INT);
            $stmt->bindParam(':date', $dayDate, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':overtime', $details['overtime'], PDO::PARAM_INT);
            $stmt->bindValue(':notes', '', PDO::PARAM_STR);
            $stmt->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Đã lưu chấm công thành công']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Lỗi database: ' . $e->getMessage()]);
}
?> 