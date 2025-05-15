<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Database connection
include '../includes/db_connect.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

$attendanceId = intval($_GET['id']);

try {
    // Get attendance record
    $stmt = $conn->prepare("SELECT a.*, e.Ten_nhan_vien, e.Phong_ban 
                           FROM Attendance a 
                           JOIN Employee e ON a.employee_id = e.id 
                           WHERE a.id = :id");
    $stmt->bindParam(':id', $attendanceId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy bản ghi chấm công']);
        exit;
    }

    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get employee details
    $employeeId = $attendance['employee_id'];
    $stmt = $conn->prepare("SELECT id, Ten_nhan_vien, Phong_ban, Chuc_vu FROM Employee WHERE id = :id");
    $stmt->bindParam(':id', $employeeId, PDO::PARAM_INT);
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get attendance details
    $stmt = $conn->prepare("SELECT * FROM AttendanceDetail 
                           WHERE attendance_id = :attendance_id 
                           ORDER BY Ngay ASC");
    $stmt->bindParam(':attendance_id', $attendanceId, PDO::PARAM_INT);
    $stmt->execute();
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare employee data
    $employeeData = [
        'Ten_nhan_vien' => $attendance['Ten_nhan_vien'],
        'Phong_ban' => $attendance['Phong_ban']
    ];

    // Prepare attendance data with month and year
    $attendanceData = $attendance;
    $attendanceData['Thang'] = $attendance['Thang'];
    $attendanceData['Nam'] = $attendance['Nam'];

    // Return the data as JSON
    echo json_encode([
        'success' => true,
        'attendance' => $attendanceData,
        'details' => $details,
        'employee' => $employeeData
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
    exit;
}
