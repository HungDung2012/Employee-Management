<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CompanyManagement";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add new assessment
        if ($_POST['action'] === 'add_assessment') {
            try {
                // Get employee information
                $employee_id = $_POST['employee'];
                $stmt = $conn->prepare("SELECT Ten_nhan_vien, Phong_ban FROM Employee WHERE id = ?");
                $stmt->execute([$employee_id]);
                $employee = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$employee) {
                    throw new Exception("Không tìm thấy thông tin nhân viên");
                }
                
                $employee_name = $employee['Ten_nhan_vien'];
                
                // Calculate average score
                $quality = $_POST['quality'];
                $efficiency = $_POST['efficiency'];
                $attitude = $_POST['attitude'];
                $communication = $_POST['communication'];
                $teamwork = $_POST['teamwork'];
                
                $avg_score = ($quality + $efficiency + $attitude + $communication + $teamwork) / 5;
                
                // Determine rating
                $rating = '';
                if ($avg_score >= 4.5) $rating = 'Xuất sắc';
                else if ($avg_score >= 3.5) $rating = 'Tốt';
                else if ($avg_score >= 2.5) $rating = 'Khá';
                else if ($avg_score >= 1.5) $rating = 'Trung bình';
                else $rating = 'Yếu';
                
                // Insert assessment record
                $stmt = $conn->prepare("INSERT INTO Assessment (Ten_nhan_vien, Thoi_gian, Nguoi_danh_gia, Diem_danh_gia, Xep_loai) 
                                        VALUES (?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $employee_name,
                    $_POST['date'],
                    $_POST['evaluator'],
                    $avg_score,
                    $rating
                ]);
                
                $success_message = "Đã thêm đánh giá mới cho nhân viên: " . $employee_name;
            } catch (PDOException $e) {
                $error_message = "Lỗi khi thêm đánh giá: " . $e->getMessage();
            } catch (Exception $e) {
                $error_message = $e->getMessage();
            }
        }
        
        // Delete assessment record
        if ($_POST['action'] === 'delete_assessment') {
            $id = $_POST['assessment_id'];

            try {
                $stmt = $conn->prepare("DELETE FROM Assessment WHERE id = ?");
                $stmt->execute([$id]);
                
                $success_message = "Đã xóa bản ghi đánh giá có ID: " . $id;
            } catch (PDOException $e) {
                $error_message = "Lỗi khi xóa bản ghi đánh giá: " . $e->getMessage();
            }
        }
    }
}

// Build search and filter query
$where_conditions = [];
$params = [];

// Search by employee name
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "e.Ten_nhan_vien LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
}

// Filter by date range
if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $where_conditions[] = "a.Thoi_gian >= ?";
    $params[] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $where_conditions[] = "a.Thoi_gian <= ?";
    $params[] = $_GET['date_to'];
}

// Filter by rating
if (isset($_GET['rating']) && !empty($_GET['rating'])) {
    $where_conditions[] = "a.Xep_loai = ?";
    $params[] = $_GET['rating'];
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Build order by
$order_by = "ORDER BY a.id DESC";
?>

<div class="assessment-container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Quản lý Đánh giá</h2>
            <button class="btn btn-primary" onclick="openAssessmentModal()">Đánh giá mới</button>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" id="assessmentFilterForm" style="display: flex; width: 100%;">
                <input type="hidden" name="page" value="assessment">
                
                <div class="filter-item" style="width:40%; flex: none;">
                    <label for="search">Tìm kiếm</label>
                    <input type="text" id="search" name="search" placeholder="Tên nhân viên..." 
                        class="form-control" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                
                <div class="filter-item">
                    <label for="date_from">Từ ngày</label>
                    <input type="date" id="date_from" name="date_from" class="form-control"
                        value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
                </div>
                
                <div class="filter-item">
                    <label for="date_to">Đến ngày</label>
                    <input type="date" id="date_to" name="date_to" class="form-control"
                        value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
                </div>
                
                <div class="filter-item">
                    <label for="rating">Xếp loại</label>
                    <select id="rating" name="rating" class="form-control">
                        <option value="">Tất cả</option>
                        <option value="Xuất sắc" <?php echo (isset($_GET['rating']) && $_GET['rating'] == 'Xuất sắc') ? 'selected' : ''; ?>>Xuất sắc</option>
                        <option value="Tốt" <?php echo (isset($_GET['rating']) && $_GET['rating'] == 'Tốt') ? 'selected' : ''; ?>>Tốt</option>
                        <option value="Khá" <?php echo (isset($_GET['rating']) && $_GET['rating'] == 'Khá') ? 'selected' : ''; ?>>Khá</option>
                        <option value="Trung bình" <?php echo (isset($_GET['rating']) && $_GET['rating'] == 'Trung bình') ? 'selected' : ''; ?>>Trung bình</option>
                        <option value="Yếu" <?php echo (isset($_GET['rating']) && $_GET['rating'] == 'Yếu') ? 'selected' : ''; ?>>Yếu</option>
                    </select>
                </div>
                
                <div class="filter-item" style="align-items: flex-end;flex-direction: row;margin-left: 5px;">
                    <button type="submit" style="margin-right: 5px;" class="btn btn-primary">Lọc</button>
                    <button type="button" class="btn btn-secondary">
                        <a href="index.php?page=assessment" style="text-decoration: none;color: white;">
                            Đặt lại
                        </a>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Assessment Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên nhân viên</th>
                    <th>Phòng ban</th>
                    <th>Mức lương</th>
                    <th>Thời gian đánh giá</th>
                    <th>Người đánh giá</th>
                    <th>Điểm đánh giá</th>
                    <th>Xếp loại</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch assessment data with filters
                $sql = "SELECT a.id, e.Ten_nhan_vien, e.Phong_ban, e.Muc_luong, a.Thoi_gian, a.Nguoi_danh_gia, a.Diem_danh_gia, a.Xep_loai 
                        FROM Assessment a 
                        JOIN Employee e ON a.Ten_nhan_vien = e.Ten_nhan_vien 
                        $where_clause $order_by";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($assessments) > 0) {
                    foreach ($assessments as $assessment) {
                        echo "<tr>";
                        echo "<td>{$assessment['id']}</td>";
                        echo "<td>{$assessment['Ten_nhan_vien']}</td>";
                        echo "<td>{$assessment['Phong_ban']}</td>";
                        echo "<td>" . number_format($assessment['Muc_luong'], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>" . date('d/m/Y', strtotime($assessment['Thoi_gian'])) . "</td>";
                        echo "<td>{$assessment['Nguoi_danh_gia']}</td>";
                        echo "<td>{$assessment['Diem_danh_gia']}</td>";
                        echo "<td>{$assessment['Xep_loai']}</td>";
                        echo "<td>
                                <button class='btn btn-info btn-sm' onclick='viewAssessmentDetails({$assessment['id']})'>Chi tiết</button>
                                <button class='btn btn-danger btn-sm' onclick='confirmDeleteAssessment({$assessment['id']})'>Xóa</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' style='text-align: center;'>Không tìm thấy dữ liệu đánh giá</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Assessment Modal -->
<div class="modal" id="assessmentModal">
    <div class="modal-content" style="width: 700px; max-width: 90%;">
        <div class="modal-header">
            <h3 class="modal-title">Đánh giá nhân viên</h3>
            <button class="close-btn" onclick="closeModal('assessmentModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="assessmentForm" method="POST">
                <input type="hidden" name="action" value="add_assessment">
                
                <div class="form-group">
                    <label for="assessment_employee">Nhân viên</label>
                    <select id="assessment_employee" name="employee" class="form-control" required>
                        <option value="">Chọn nhân viên</option>
                        <?php
                        // Fetch employees for dropdown
                        $stmt = $conn->prepare("SELECT id, Ten_nhan_vien FROM Employee ORDER BY Ten_nhan_vien");
                        $stmt->execute();
                        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($employees as $emp) {
                            echo "<option value='{$emp['id']}'>{$emp['Ten_nhan_vien']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="assessment_date">Ngày đánh giá</label>
                    <input type="date" id="assessment_date" name="date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="assessment_evaluator">Người đánh giá</label>
                    <input type="text" id="assessment_evaluator" name="evaluator" class="form-control" required>
                </div>
                
                <h4 class="mt-4">Tiêu chí đánh giá</h4>
                
                <div class="assessment-criteria">
                    <div class="criteria-item">
                        <label>1. Chất lượng công việc</label>
                        <div class="rating-group">
                            <?php 
                            for($i = 1; $i <= 5; $i++) {
                                echo "<label><input type='radio' name='quality' value='$i'> $i</label>";
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="criteria-item">
                        <label>2. Hiệu quả làm việc</label>
                        <div class="rating-group">
                            <?php 
                            for($i = 1; $i <= 5; $i++) {
                                echo "<label><input type='radio' name='efficiency' value='$i'> $i</label>";
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="criteria-item">
                        <label>3. Tinh thần làm việc</label>
                        <div class="rating-group">
                            <?php 
                            for($i = 1; $i <= 5; $i++) {
                                echo "<label><input type='radio' name='attitude' value='$i'> $i</label>";
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="criteria-item">
                        <label>4. Kỹ năng giao tiếp</label>
                        <div class="rating-group">
                            <?php 
                            for($i = 1; $i <= 5; $i++) {
                                echo "<label><input type='radio' name='communication' value='$i'> $i</label>";
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="criteria-item">
                        <label>5. Khả năng làm việc nhóm</label>
                        <div class="rating-group">
                            <?php 
                            for($i = 1; $i <= 5; $i++) {
                                echo "<label><input type='radio' name='teamwork' value='$i'> $i</label>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="assessment_comments">Nhận xét</label>
                    <textarea id="assessment_comments" name="comments" class="form-control" rows="3"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('assessmentModal')">Hủy</button>
            <button class="btn btn-primary" onclick="submitAssessment()">Lưu đánh giá</button>
        </div>
    </div>
</div>

<!-- Assessment Details Modal -->
<div class="modal" id="assessmentDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Chi tiết đánh giá</h3>
            <button class="close-btn" onclick="closeModal('assessmentDetailsModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div id="assessment-details-content">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('assessmentDetailsModal')">Đóng</button>
            <button class="btn btn-primary" onclick="printAssessmentDetails()">In đánh giá</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteAssessmentModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Xác nhận xóa</h3>
            <button class="close-btn" onclick="closeModal('deleteAssessmentModal')">&times;</button>
        </div>
        <div class="modal-body" style="height: 7vh;">
            <p>Bạn có chắc chắn muốn xóa đánh giá này?</p>
            <form id="deleteAssessmentForm" method="POST">
                <input type="hidden" name="action" value="delete_assessment">
                <input type="hidden" id="delete_assessment_id" name="assessment_id">
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('deleteAssessmentModal')">Hủy</button>
            <button class="btn btn-danger" onclick="document.getElementById('deleteAssessmentForm').submit()">Xóa</button>
        </div>
    </div>
</div>

<script>
// Function has been replaced by server-side filtering
// function searchAssessment() { ... }

// Function has been replaced by server-side filtering
// function applyAssessmentFilters() { ... }

// Function has been replaced by server-side filtering
// function resetAssessmentFilters() { ... }

function openAssessmentModal() {
    // Set today's date as default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('assessment_date').value = today;
    
    document.getElementById('assessmentModal').classList.add('show');
}

function submitAssessment() {
    // Validate form
    const form = document.getElementById('assessmentForm');
    const employee = document.getElementById('assessment_employee').value;
    if (!employee) {
        alert('Vui lòng chọn nhân viên');
        return;
    }
    
    const evaluator = document.getElementById('assessment_evaluator').value;
    if (!evaluator) {
        alert('Vui lòng nhập tên người đánh giá');
        return;
    }
    
    // Kiểm tra các tiêu chí đánh giá
    if (!document.querySelector('input[name="quality"]:checked')) {
        alert('Vui lòng đánh giá Chất lượng công việc');
        return;
    }
    
    if (!document.querySelector('input[name="efficiency"]:checked')) {
        alert('Vui lòng đánh giá Hiệu quả làm việc');
        return;
    }
    
    if (!document.querySelector('input[name="attitude"]:checked')) {
        alert('Vui lòng đánh giá Tinh thần làm việc');
        return;
    }
    
    if (!document.querySelector('input[name="communication"]:checked')) {
        alert('Vui lòng đánh giá Kỹ năng giao tiếp');
        return;
    }
    
    if (!document.querySelector('input[name="teamwork"]:checked')) {
        alert('Vui lòng đánh giá Khả năng làm việc nhóm');
        return;
    }
    
    // Gửi form
    form.submit();
}

function viewAssessmentDetails(id) {
    // In a real application, you would fetch the assessment details by ID
    // For this example, we'll use dummy data
    const employeeName = 'Nguyễn Văn A';
    const department = 'Kỹ thuật';
    const date = '15/05/2023';
    const evaluator = 'Trần Văn B';
    
    let detailsHTML = `
        <div class="assessment-details">
            <h4>PHIẾU ĐÁNH GIÁ NHÂN VIÊN</h4>
            <p><strong>Nhân viên:</strong> ${employeeName}</p>
            <p><strong>Phòng ban:</strong> ${department}</p>
            <p><strong>Ngày đánh giá:</strong> ${date}</p>
            <p><strong>Người đánh giá:</strong> ${evaluator}</p>
            <hr>
            <h5>Kết quả đánh giá</h5>
            <table class="details-table">
                <tr>
                    <td>1. Chất lượng công việc:</td>
                    <td>5/5</td>
                </tr>
                <tr>
                    <td>2. Hiệu quả làm việc:</td>
                    <td>4/5</td>
                </tr>
                <tr>
                    <td>3. Tinh thần làm việc:</td>
                    <td>5/5</td>
                </tr>
                <tr>
                    <td>4. Kỹ năng giao tiếp:</td>
                    <td>3/5</td>
                </tr>
                <tr>
                    <td>5. Khả năng làm việc nhóm:</td>
                    <td>4/5</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Điểm trung bình:</strong></td>
                    <td><strong>4.2/5</strong></td>
                </tr>
                <tr>
                    <td><strong>Xếp loại:</strong></td>
                    <td><strong>Tốt</strong></td>
                </tr>
            </table>
            <div class="comments-section">
                <h5>Nhận xét</h5>
                <p>Nhân viên có tinh thần làm việc tốt, hoàn thành công việc đúng thời hạn. Cần cải thiện kỹ năng giao tiếp.</p>
            </div>
        </div>
    `;
    
    document.getElementById('assessment-details-content').innerHTML = detailsHTML;
    document.getElementById('assessmentDetailsModal').classList.add('show');
}

function deleteAssessment(id) {
    if (confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) {
        // In a real application, you would send a delete request
        alert('Đã xóa đánh giá ID: ' + id);
    }
}

function printAssessmentDetails() {
    const content = document.getElementById('assessment-details-content').innerHTML;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <html>
        <head>
            <title>Phiếu đánh giá</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .assessment-details { max-width: 800px; margin: 0 auto; padding: 20px; }
                .details-table { width: 100%; border-collapse: collapse; }
                .details-table td { padding: 8px; border-bottom: 1px solid #ddd; }
                .total-row { font-weight: bold; border-top: 2px solid #000; }
                .comments-section { margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="assessment-details">
                ${content}
            </div>
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// Replace delete function with confirmation modal
function confirmDeleteAssessment(id) {
    document.getElementById('delete_assessment_id').value = id;
    document.getElementById('deleteAssessmentModal').classList.add('show');
}
</script>

<style>
.search-container {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    padding: 15px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.search-container input {
    flex: 1;
}

.assessment-criteria {
    margin-bottom: 20px;
}

.criteria-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.rating-group {
    display: flex;
    gap: 15px;
}

.rating-group label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.details-table {
    width: 100%;
    border-collapse: collapse;
}

.details-table td {
    padding: 8px;
    border-bottom: 1px solid #ddd;
}

.total-row {
    border-top: 2px solid #000;
    font-weight: bold;
}

.assessment-details {
    padding: 15px;
}

.assessment-details h4 {
    text-align: center;
    margin-bottom: 20px;
}

.comments-section {
    margin-top: 20px;
    padding-top: 10px;
    border-top: 1px solid #ddd;
}
</style>
