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

// Build search and filter query
$where_conditions = [];
$params = [];

// Search by employee name
if (isset($_GET['name']) && !empty($_GET['name'])) {
    $where_conditions[] = "Ten_nhan_vien LIKE ?";
    $params[] = "%" . $_GET['name'] . "%";
}

// Filter by department
if (isset($_GET['department']) && !empty($_GET['department'])) {
    $where_conditions[] = "Phong_ban = ?";
    $params[] = $_GET['department'];
}

// Build WHERE clause
$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Build ORDER BY clause
$order_by = "ORDER BY id";
if (isset($_GET['salary_type']) && !empty($_GET['salary_type'])) {
    $salary_column = $_GET['salary_type'];
    $valid_columns = ['Luong_co_ban', 'Luong_thuc_te', 'Phu_cap', 'Luong_thuong', 'Thuc_lanh'];
    
    // Validate column name to prevent SQL injection
    if (in_array($salary_column, $valid_columns)) {
        $order_direction = (isset($_GET['order']) && $_GET['order'] === 'desc') ? 'DESC' : 'ASC';
        $order_by = "ORDER BY $salary_column $order_direction";
    }
}

// Fetch departments for filter
$stmt = $conn->prepare("SELECT DISTINCT Ten_phong_ban FROM Department ORDER BY Ten_phong_ban");
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="salary-container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Quản lý Lương thưởng</h2>
        </div>
    
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" id="salaryFilterForm" style="display: flex; width: 100%;">
                <input type="hidden" name="page" value="salary">

                <div class="filter-item" style="width:40%; flex: none;">
                    <label for="name">Tên nhân viên</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Nhập tên..." 
                           value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
                </div>
                
                <div class="filter-item">
                    <label for="department">Phòng ban</label>
                    <select id="department" name="department" class="form-control">
                        <option value="">Tất cả</option>
                        <?php
                        foreach ($departments as $dept) {
                            $selected = (isset($_GET['department']) && $_GET['department'] == $dept['Ten_phong_ban']) ? 'selected' : '';
                            echo "<option value='{$dept['Ten_phong_ban']}' $selected>{$dept['Ten_phong_ban']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="salary_type">Loại lương</label>
                    <select id="salary_type" name="salary_type" class="form-control">
                        <option value="Luong_co_ban" <?php echo (isset($_GET['salary_type']) && $_GET['salary_type'] == 'Luong_co_ban') ? 'selected' : ''; ?>>Lương cơ bản</option>
                        <option value="Luong_thuc_te" <?php echo (isset($_GET['salary_type']) && $_GET['salary_type'] == 'Luong_thuc_te') ? 'selected' : ''; ?>>Lương thực tế</option>
                        <option value="Phu_cap" <?php echo (isset($_GET['salary_type']) && $_GET['salary_type'] == 'Phu_cap') ? 'selected' : ''; ?>>Phụ cấp</option>
                        <option value="Thuc_lanh" <?php echo (isset($_GET['salary_type']) && $_GET['salary_type'] == 'Thuc_lanh') ? 'selected' : ''; ?>>Thực lãnh</option>
                    </select>
                </div>
                
                <div class="filter-item">
                    <label for="order">Sắp xếp</label>
                    <select id="order" name="order" class="form-control">
                        <option value="asc" <?php echo (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'selected' : ''; ?>>Tăng dần</option>
                        <option value="desc" <?php echo (isset($_GET['order']) && $_GET['order'] == 'desc') ? 'selected' : ''; ?>>Giảm dần</option>
                    </select>
                </div>
                
                <div class="filter-item" style="align-items: flex-end;flex-direction: row;margin-left: 5px;">
                    <button type="submit" style="margin-right: 5px;" class="btn btn-primary">Lọc</button>
                    <button type="button" class="btn btn-secondary">
                        <a href="index.php?page=salary" style="text-decoration: none;color: white;">
                            Đặt lại
                        </a>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Salary Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên nhân viên</th>
                    <th>Lương cơ bản</th>
                    <th>Lương thực tế</th>
                    <th>Phụ cấp</th>
                    <th>Lương thưởng</th>
                    <th>Các khoản trừ</th>
                    <th>Thuế</th>
                    <th>Thực lãnh</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch salary data with filters
                $sql = "SELECT id, Ten_nhan_vien, Phong_ban, Luong_co_ban, Luong_thuc_te, Phu_cap, Luong_thuong, Cac_khoan_tru, Thue, Thuc_lanh FROM Employee $where_clause $order_by";
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $salaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($salaries) > 0) {
                    foreach ($salaries as $salary) {
                        echo "<tr>";
                        echo "<td>{$salary['id']}</td>";
                        echo "<td>{$salary['Ten_nhan_vien']}</td>";
                        echo "<td>" . number_format($salary['Luong_co_ban'], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>" . number_format($salary['Luong_thuc_te'], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>" . number_format($salary['Phu_cap'], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>" . number_format($salary['Luong_thuong'], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>" . number_format($salary['Cac_khoan_tru'], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>" . number_format($salary['Thue'], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>" . number_format($salary['Thuc_lanh'], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>
                                <button class='btn btn-warning btn-sm' onclick='openEditSalaryModal({$salary['id']})'>Sửa</button>
                                <button class='btn btn-info btn-sm' onclick='viewSalaryDetails({$salary['id']})'>Chi tiết</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' style='text-align: center;'>Không tìm thấy dữ liệu lương</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Salary Modal -->
<div class="modal" id="editSalaryModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Chỉnh sửa thông tin lương</h3>
            <button class="close-btn" onclick="closeModal('editSalaryModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editSalaryForm" method="POST">
                <input type="hidden" name="action" value="edit_salary">
                <input type="hidden" id="edit_salary_id" name="id">
                
                <div class="form-group">
                    <label for="edit_employee_name">Tên nhân viên</label>
                    <input type="text" id="edit_employee_name" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label for="edit_base_salary">Lương cơ bản (VNĐ)</label>
                    <input type="number" id="edit_base_salary" name="base_salary" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_actual_salary">Lương thực tế (VNĐ)</label>
                    <input type="number" id="edit_actual_salary" name="actual_salary" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_allowance">Phụ cấp (VNĐ)</label>
                    <input type="number" id="edit_allowance" name="allowance" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_bonus">Lương thưởng (VNĐ)</label>
                    <input type="number" id="edit_bonus" name="bonus" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_deductions">Các khoản trừ (VNĐ)</label>
                    <input type="number" id="edit_deductions" name="deductions" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_tax">Thuế (VNĐ)</label>
                    <input type="number" id="edit_tax" name="tax" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_net_salary">Thực lãnh (VNĐ)</label>
                    <input type="number" id="edit_net_salary" name="net_salary" class="form-control" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editSalaryModal')">Hủy</button>
            <button class="btn btn-primary" onclick="document.getElementById('editSalaryForm').submit()">Lưu thay đổi</button>
        </div>
    </div>
</div>

<!-- Salary Details Modal -->
<div class="modal" id="salaryDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Chi tiết lương</h3>
            <button class="close-btn" onclick="closeModal('salaryDetailsModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div id="salary-details-content">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('salaryDetailsModal')">Đóng</button>
            <button class="btn btn-primary" onclick="printSalaryDetails()">In phiếu lương</button>
        </div>
    </div>
</div>

<script>
function openEditSalaryModal(id) {
    // Fetch employee salary data
    fetch('ajax/get_salary_details.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_salary_id').value = id;
                document.getElementById('edit_employee_name').value = data.employee.Ten_nhan_vien;
                document.getElementById('edit_base_salary').value = data.employee.Luong_co_ban;
                document.getElementById('edit_actual_salary').value = data.employee.Luong_thuc_te;
                document.getElementById('edit_allowance').value = data.employee.Phu_cap;
                document.getElementById('edit_bonus').value = data.employee.Luong_thuong;
                document.getElementById('edit_deductions').value = data.employee.Cac_khoan_tru;
                document.getElementById('edit_tax').value = data.employee.Thue;
                document.getElementById('edit_net_salary').value = data.employee.Thuc_lanh;
                
                document.getElementById('editSalaryModal').classList.add('show');
            }
        });
}

function viewSalaryDetails(id) {
    // In a real application, you would fetch the salary details by ID
    // For this example, we'll use dummy data
    const employeeName = 'Nhân viên #' + id;
    const month = 'Tháng ' + (new Date().getMonth() + 1) + '/' + new Date().getFullYear();
    
    let detailsHTML = `
        <div class="salary-slip">
            <h4>PHIẾU LƯƠNG</h4>
            <p><strong>Nhân viên:</strong> ${employeeName}</p>
            <p><strong>Kỳ lương:</strong> ${month}</p>
            <hr>
            <table class="details-table">
                <tr>
                    <td>Lương cơ bản:</td>
                    <td>15.000.000 VNĐ</td>
                </tr>
                <tr>
                    <td>Lương thực tế:</td>
                    <td>18.000.000 VNĐ</td>
                </tr>
                <tr>
                    <td>Phụ cấp:</td>
                    <td>2.000.000 VNĐ</td>
                </tr>
                <tr>
                    <td>Lương thưởng:</td>
                    <td>1.500.000 VNĐ</td>
                </tr>
                <tr>
                    <td>Các khoản trừ:</td>
                    <td>500.000 VNĐ</td>
                </tr>
                <tr>
                    <td>Thuế:</td>
                    <td>1.000.000 VNĐ</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Thực lãnh:</strong></td>
                    <td><strong>20.000.000 VNĐ</strong></td>
                </tr>
            </table>
        </div>
    `;
    
    document.getElementById('salary-details-content').innerHTML = detailsHTML;
    document.getElementById('salaryDetailsModal').classList.add('show');
}

function printSalaryDetails() {
    const content = document.getElementById('salary-details-content').innerHTML;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <html>
        <head>
            <title>Phiếu lương</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .salary-slip { max-width: 800px; margin: 0 auto; padding: 20px; }
                .details-table { width: 100%; border-collapse: collapse; }
                .details-table td { padding: 8px; border-bottom: 1px solid #ddd; }
                .total-row { font-weight: bold; border-top: 2px solid #000; }
            </style>
        </head>
        <body>
            <div class="salary-slip">
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

.salary-slip {
    padding: 15px;
}

.salary-slip h4 {
    text-align: center;
    margin-bottom: 20px;
}
</style>
