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
        // Add new employee
        if ($_POST['action'] === 'add_employee') {
            $name = $_POST['emp_name'];
            $gender = $_POST['emp_gender'];
            $department = $_POST['emp_department'];
            $position = $_POST['emp_position'];
            $salary = $_POST['emp_salary'];
            $start_date = $_POST['emp_start_date'];
            $end_date = $_POST['emp_end_date'];
            $contract_type = $_POST['emp_contract_type'];

            try {
                $stmt = $conn->prepare("INSERT INTO Employee (Ten_nhan_vien, Gioi_tinh, Phong_ban, Chuc_vu, Muc_luong, Ngay_bat_dau_hop_dong, Ngay_ket_thuc_hop_dong, Loai_hop_dong, Luong_co_ban, Luong_thuc_te) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $gender, $department, $position, $salary, $start_date, $end_date, $contract_type, $salary, $salary]);
                $success_message = "Đã thêm nhân viên mới: $name";
            } catch (PDOException $e) {
                $error_message = "Lỗi khi thêm nhân viên: " . $e->getMessage();
            }
        }

        // Edit employee
        else if ($_POST['action'] === 'edit_employee') {
            $id = $_POST['emp_id'];
            $name = $_POST['emp_name'];
            $gender = $_POST['emp_gender'];
            $department = $_POST['emp_department'];
            $position = $_POST['emp_position'];
            $salary = $_POST['emp_salary'];
            $start_date = $_POST['emp_start_date'];
            $end_date = $_POST['emp_end_date'];
            $contract_type = $_POST['emp_contract_type'];

            try {
                $stmt = $conn->prepare("UPDATE Employee SET Ten_nhan_vien = ?, Gioi_tinh = ?, Phong_ban = ?, Chuc_vu = ?, Muc_luong = ?, Ngay_bat_dau_hop_dong = ?, Ngay_ket_thuc_hop_dong = ?, Loai_hop_dong = ?, Luong_co_ban = ?, Luong_thuc_te = ? WHERE id = ?");
                $stmt->execute([$name, $gender, $department, $position, $salary, $start_date, $end_date, $contract_type, $salary, $salary, $id]);
                $success_message = "Đã cập nhật thông tin nhân viên: $name";
            } catch (PDOException $e) {
                $error_message = "Lỗi khi cập nhật nhân viên: " . $e->getMessage();
            }
        }

        // Delete employee
        else if ($_POST['action'] === 'delete_employee') {
            $id = $_POST['emp_id'];

            try {
                $stmt = $conn->prepare("DELETE FROM Employee WHERE id = ?");
                $stmt->execute([$id]);
                $success_message = "Đã xóa nhân viên có ID: $id";
            } catch (PDOException $e) {
                $error_message = "Lỗi khi xóa nhân viên: " . $e->getMessage();
            }
        }
    }
}

// Build search and filter query
// Tạo mảng chứa điều kiện ($where_conditions) và giá trị cần gán ($params) cho câu lệnh prepare


$where_conditions = [];
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "Ten_nhan_vien LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
}

if (isset($_GET['department']) && !empty($_GET['department'])) {
    $where_conditions[] = "Phong_ban = ?";
    $params[] = $_GET['department'];
}

if (isset($_GET['gender']) && !empty($_GET['gender'])) {
    $where_conditions[] = "Gioi_tinh = ?";
    $params[] = $_GET['gender'];
}

if (isset($_GET['salary']) && !empty($_GET['salary'])) {
    switch ($_GET['salary']) {
        case '1':
            $where_conditions[] = "Muc_luong < 15000000";
            break;
        case '2':
            $where_conditions[] = "Muc_luong BETWEEN 15000000 AND 20000000";
            break;
        case '3':
            $where_conditions[] = "Muc_luong BETWEEN 20000000 AND 25000000";
            break;
        case '4':
            $where_conditions[] = "Muc_luong > 25000000";
            break;
    }
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Build order by
$order_by = "ORDER BY id";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'name_asc':
            $order_by = "ORDER BY Ten_nhan_vien ASC";
            break;
        case 'name_desc':
            $order_by = "ORDER BY Ten_nhan_vien DESC";
            break;
        case 'salary_asc':
            $order_by = "ORDER BY Muc_luong ASC";
            break;
        case 'salary_desc':
            $order_by = "ORDER BY Muc_luong DESC";
            break;
    }
}

// Fetch departments for filter
$stmt = $conn->prepare("SELECT DISTINCT Ten_phong_ban FROM Department ORDER BY Ten_phong_ban");
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="employees-container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Quản lý nhân viên</h2>
            <button class="btn btn-primary" onclick="openAddEmployeeModal()">Thêm nhân viên mới</button>
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

        <div class="filter-section">
            <form method="GET" id="filterForm">
                <input type="hidden" name="page" value="employees">
                <div class="filter-item">
                    <label for="search">Tìm kiếm</label>
                    <input type="text" id="search" name="search" class="search-input" placeholder="Tên nhân viên...">
                </div>
                <div class="filter-item">
                    <label for="department">Phòng ban</label>
                    <select id="department" name="department">
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
                    <label for="gender">Giới tính</label>
                    <select id="gender" name="gender">
                        <option value="">Tất cả</option>
                        <option value="Nam" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                        <option value="Nữ" <?php echo (isset($_GET['gender']) && $_GET['gender'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="salary">Mức lương</label>
                    <select id="salary" name="salary">
                        <option value="">Tất cả</option>
                        <option value="1" <?php echo (isset($_GET['salary']) && $_GET['salary'] == '1') ? 'selected' : ''; ?>>Dưới 15 triệu</option>
                        <option value="2" <?php echo (isset($_GET['salary']) && $_GET['salary'] == '2') ? 'selected' : ''; ?>>15-20 triệu</option>
                        <option value="3" <?php echo (isset($_GET['salary']) && $_GET['salary'] == '3') ? 'selected' : ''; ?>>20-25 triệu</option>
                        <option value="4" <?php echo (isset($_GET['salary']) && $_GET['salary'] == '4') ? 'selected' : ''; ?>>Trên 25 triệu</option>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="sort">Sắp xếp</label>
                    <select id="sort" name="sort">
                        <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Tên (A-Z)</option>
                        <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Tên (Z-A)</option>
                        <option value="salary_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'salary_asc') ? 'selected' : ''; ?>>Lương (Tăng dần)</option>
                        <option value="salary_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'salary_desc') ? 'selected' : ''; ?>>Lương (Giảm dần)</option>
                    </select>
                </div>
                <div class="filter-item" style="align-items: flex-end;flex-direction: row;margin-left: 5px;">
                    <button type="submit" style="margin-right: 5px;" class="btn btn-primary">Lọc</button>
                    <button type="button" class="btn btn-secondary">
                        <a href="index.php?page=employees" style="text-decoration: none;color: white;">
                            Xóa bộ lọc
                        </a>
                    </button>
                </div>
            </form>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên nhân viên</th>
                    <th>Giới tính</th>
                    <th>Phòng ban</th>
                    <th>Chức vụ</th>
                    <th>Loại Hợp đồng</th>
                    <th>Mức lương</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch employee data with filters
                $sql = "SELECT id, Ten_nhan_vien, Gioi_tinh, Phong_ban, Chuc_vu, Muc_luong, Ngay_bat_dau_hop_dong, Ngay_ket_thuc_hop_dong, Loai_hop_dong FROM Employee $where_clause $order_by";
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($employees as $emp) {
                    echo "<tr>";
                    echo "<td>{$emp['id']}</td>";
                    echo "<td class='emp-name'>{$emp['Ten_nhan_vien']}</td>";
                    echo "<td class='emp-gen'>{$emp['Gioi_tinh']}</td>";
                    echo "<td class='emp-phong'>{$emp['Phong_ban']}</td>";
                    echo "<td class='emp-chuc'>{$emp['Chuc_vu']}</td>";
                    echo "<td >{$emp['Loai_hop_dong']}</td>";
                    echo "<td>" . number_format($emp['Muc_luong'], 0, ',', '.') . " VNĐ</td>";
                    echo "<td>
                            <button class='btn btn-warning btn-sm' onclick='openEditEmployeeModal({$emp['id']}, \"" . htmlspecialchars($emp['Ten_nhan_vien']) . "\", \"" . htmlspecialchars($emp['Gioi_tinh']) . "\", \"" . htmlspecialchars($emp['Phong_ban']) . "\", \"" . htmlspecialchars($emp['Chuc_vu']) . "\", {$emp['Muc_luong']}, \"" . htmlspecialchars($emp['Ngay_bat_dau_hop_dong']) . "\", \"" . htmlspecialchars($emp['Ngay_ket_thuc_hop_dong']) . "\", \"" . htmlspecialchars($emp['Loai_hop_dong']) . "\")'>Sửa</button>
                            <button class='btn btn-danger btn-sm' onclick='confirmDeleteEmployee({$emp['id']}, \"" . htmlspecialchars($emp['Ten_nhan_vien']) . "\")'>Xóa</button>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal" id="addEmployeeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Thêm nhân viên mới</h3>
            <button class="close-btn" onclick="closeModal('addEmployeeModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addEmployeeForm" method="POST">
                <input type="hidden" name="action" value="add_employee">
                <div class="form-group">
                    <label for="emp_name">Họ và tên</label>
                    <input type="text" id="emp_name" name="emp_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="emp_gender">Giới tính</label>
                    <select id="emp_gender" name="emp_gender" class="form-control" required>
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="emp_department">Phòng ban</label>
                    <select id="emp_department" name="emp_department" class="form-control" required>
                        <?php
                        foreach ($departments as $dept) {
                            echo "<option value='{$dept['Ten_phong_ban']}'>{$dept['Ten_phong_ban']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="emp_position">Chức vụ</label>
                    <input type="text" id="emp_position" name="emp_position" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="emp_salary">Mức lương (VNĐ)</label>
                    <input type="number" id="emp_salary" name="emp_salary" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="emp_start_date">Ngày bắt đầu hợp đồng</label>
                    <input type="date" id="emp_start_date" name="emp_start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="emp_end_date">Ngày kết thúc hợp đồng</label>
                    <input type="date" id="emp_end_date" name="emp_end_date" class="form-control">
                </div>
                <div class="form-group">
                    <label for="emp_contract_type">Loại hợp đồng</label>
                    <select id="emp_contract_type" name="emp_contract_type" class="form-control" required>
                        <option value="Có thời hạn">Có thời hạn</option>
                        <option value="Không thời hạn">Không thời hạn</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('addEmployeeModal')">Hủy</button>
            <button class="btn btn-primary" onclick="validateAddEmployeeForm()">Lưu</button>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal" id="editEmployeeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Chỉnh sửa thông tin nhân viên</h3>
            <button class="close-btn" onclick="closeModal('editEmployeeModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editEmployeeForm" method="POST">
                <input type="hidden" name="action" value="edit_employee">
                <input type="hidden" id="edit_emp_id" name="emp_id">
                <div class="form-group">
                    <label for="edit_emp_name">Họ và tên</label>
                    <input type="text" id="edit_emp_name" name="emp_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_emp_gender">Giới tính</label>
                    <select id="edit_emp_gender" name="emp_gender" class="form-control" required>
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_emp_department">Phòng ban</label>
                    <select id="edit_emp_department" name="emp_department" class="form-control" required>
                        <?php
                        foreach ($departments as $dept) {
                            echo "<option value='{$dept['Ten_phong_ban']}'>{$dept['Ten_phong_ban']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_emp_position">Chức vụ</label>
                    <input type="text" id="edit_emp_position" name="emp_position" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_emp_salary">Mức lương (VNĐ)</label>
                    <input type="number" id="edit_emp_salary" name="emp_salary" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_emp_start_date">Ngày bắt đầu hợp đồng</label>
                    <input type="date" id="edit_emp_start_date" name="emp_start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_emp_end_date">Ngày kết thúc hợp đồng</label>
                    <input type="date" id="edit_emp_end_date" name="emp_end_date" class="form-control">
                </div>
                <div class="form-group">
                    <label for="edit_emp_contract_type">Loại hợp đồng</label>
                    <select id="edit_emp_contract_type" name="emp_contract_type" class="form-control" required>
                        <option value="Có thời hạn">Có thời hạn</option>
                        <option value="Không thời hạn">Không thời hạn</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editEmployeeModal')">Hủy</button>
            <button class="btn btn-primary" onclick="validateEditEmployeeForm()">Lưu thay đổi</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteEmployeeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Xác nhận xóa</h3>
            <button class="close-btn" onclick="closeModal('deleteEmployeeModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa nhân viên <span id="delete_emp_name"></span>?</p>
            <form id="deleteEmployeeForm" method="POST">
                <input type="hidden" name="action" value="delete_employee">
                <input type="hidden" id="delete_emp_id" name="emp_id">
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('deleteEmployeeModal')">Hủy</button>
            <button class="btn btn-danger" onclick="document.getElementById('deleteEmployeeForm').submit()">Xóa</button>
        </div>
    </div>
</div>

<script>
    function openAddEmployeeModal() {
        document.getElementById('addEmployeeModal').classList.add('show');
    }

    function openEditEmployeeModal(id, name, gender, department, position, salary, startDate, endDate, contractType) {
        document.getElementById('edit_emp_id').value = id;
        document.getElementById('edit_emp_name').value = name;
        document.getElementById('edit_emp_gender').value = gender;
        document.getElementById('edit_emp_department').value = department;
        document.getElementById('edit_emp_position').value = position;
        document.getElementById('edit_emp_salary').value = salary;
        document.getElementById('edit_emp_start_date').value = startDate;
        document.getElementById('edit_emp_end_date').value = endDate;
        document.getElementById('edit_emp_contract_type').value = contractType;
        document.getElementById('editEmployeeModal').classList.add('show');
    }

    function confirmDeleteEmployee(id, name) {
        document.getElementById('delete_emp_id').value = id;
        document.getElementById('delete_emp_name').textContent = name;
        document.getElementById('deleteEmployeeModal').classList.add('show');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    function clearFilters() {
        window.location.href = window.location.pathname;
    }

    // Simple search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('.search-input');
        const tableRows = document.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            tableRows.forEach(row => {
                const name = row.querySelector('.emp-name').textContent.toLowerCase();


                if (name.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // Validation functions
    function validateAddEmployeeForm() {
        const form = document.getElementById('addEmployeeForm');
        const requiredFields = form.querySelectorAll('[required]');
        const emptyFields = [];

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                emptyFields.push(field.labels[0].textContent);
            }
        });

        if (emptyFields.length > 0) {
            alert('Vui lòng điền đầy đủ thông tin cho các trường sau:\n- ' + emptyFields.join('\n- '));
            return false;
        }
        
        form.submit();
        return true;
    }

    function validateEditEmployeeForm() {
        const form = document.getElementById('editEmployeeForm');
        const requiredFields = form.querySelectorAll('[required]');
        const emptyFields = [];

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                emptyFields.push(field.labels[0].textContent);
            }
        });

        if (emptyFields.length > 0) {
            alert('Vui lòng điền đầy đủ thông tin cho các trường sau:\n- ' + emptyFields.join('\n- '));
            return false;
        }
        
        form.submit();
        return true;
    }
</script>