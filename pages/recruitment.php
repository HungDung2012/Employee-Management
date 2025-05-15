<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CompanyManagement";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add new candidate
        if ($_POST['action'] === 'add') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $education_level = $_POST['education_level'];
            $salary = $_POST['salary'];
            $status = $_POST['status'];

            try {
                $stmt = $pdo->prepare("INSERT INTO recruitment (full_name, email, education_level, salary, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $education_level, $salary, $status]);
                $success_message = "Đã thêm ứng viên mới: $name";
            } catch (PDOException $e) {
                $error_message = "Lỗi khi thêm ứng viên: " . $e->getMessage();
            }
        }

        // Edit candidate
        else if ($_POST['action'] === 'edit') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $education_level = $_POST['education_level'];
            $salary = $_POST['salary'];
            $status = $_POST['status'];

            try {
                $stmt = $pdo->prepare("UPDATE recruitment SET full_name = ?, email = ?, education_level = ?, salary = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $email, $education_level, $salary, $status, $id]);
                $success_message = "Đã cập nhật thông tin ứng viên: $name";
            } catch (PDOException $e) {
                $error_message = "Lỗi khi cập nhật ứng viên: " . $e->getMessage();
            }
        }

        // Delete candidate
        else if ($_POST['action'] === 'delete') {
            $id = $_POST['id'];

            try {
                $stmt = $pdo->prepare("DELETE FROM recruitment WHERE id = ?");
                $stmt->execute([$id]);
                $success_message = "Đã xóa ứng viên có ID: $id";
            } catch (PDOException $e) {
                $error_message = "Lỗi khi xóa ứng viên: " . $e->getMessage();
            }
        }
    }
}

// Fetch all candidates
try {
    $stmt = $pdo->query("SELECT * FROM recruitment ORDER BY id");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Lỗi khi lấy dữ liệu: " . $e->getMessage();
    $candidates = [];
}
?>

<div class="recruitment-container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Quản lý tuyển dụng</h2>
            <button class="btn btn-primary" onclick="openAddModal()">Thêm ứng viên mới</button>
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
            <div class="filter-item" style="max-width: 75%;">
                <label for="search">Tìm kiếm</label>
                <input type="text" id="search" name="search" class="search-input" placeholder="Tên ứng viên...">
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>Trình độ học vấn</th>
                    <th>Mức lương kỳ vọng</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($candidates as $index => $candidate): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td class='emp-name'><?php echo htmlspecialchars($candidate['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($candidate['email']); ?></td>
                        <td><?php echo htmlspecialchars($candidate['education_level']); ?></td>
                        <td><?php echo number_format($candidate['salary']) . ' VNĐ'; ?></td>
                        <td>
                            <span class="badge <?php echo $candidate['status'] === 'Đã tuyển' ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo htmlspecialchars($candidate['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="openEditModal(<?php echo $candidate['id']; ?>, 
                                                                                        '<?php echo htmlspecialchars($candidate['full_name']); ?>', 
                                                                                        '<?php echo htmlspecialchars($candidate['email']); ?>', 
                                                                                        '<?php echo htmlspecialchars($candidate['education_level']); ?>', 
                                                                                        '<?php echo $candidate['salary']; ?>', 
                                                                                        '<?php echo htmlspecialchars($candidate['status']); ?>')">
                                Sửa
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $candidate['id']; ?>, '<?php echo htmlspecialchars($candidate['full_name']); ?>')">Xóa</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Candidate Modal -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Thêm ứng viên mới</h3>
            <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addForm" method="POST">
                <input type="hidden" name="action" value="add" required>
                <div class="form-group">
                    <label for="name">Họ và tên</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="education_level">Trình độ học vấn</label>
                    <select id="education_level" name="education_level" class="form-control" required>
                        <option value="">Chọn trình độ</option>
                        <option value="9/12">9/12</option>
                        <option value="12/12">12/12</option>
                        <option value="Cao đẳng">Cao đẳng</option>
                        <option value="Đại học">Đại học</option>
                        <option value="Thạc sĩ">Thạc sĩ</option>
                        <option value="Tiến sĩ">Tiến sĩ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="salary">Mức lương kỳ vọng (VNĐ)</label>
                    <input type="number" id="salary" name="salary" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Chưa tuyển">Chưa tuyển</option>
                        <option value="Đã tuyển">Đã tuyển</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('addModal')">Hủy</button>
            <button class="btn btn-primary" onclick="validateAddForm()">Lưu</button>
        </div>
    </div>
</div>

<!-- Edit Candidate Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Chỉnh sửa thông tin ứng viên</h3>
            <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_name">Họ và tên</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_education_level">Trình độ học vấn</label>
                    <select id="edit_education_level" name="education_level" class="form-control" required>
                        <option value="">Chọn trình độ</option>
                        <option value="9/12">9/12</option>
                        <option value="12/12">12/12</option>
                        <option value="Cao đẳng">Cao đẳng</option>
                        <option value="Đại học">Đại học</option>
                        <option value="Thạc sĩ">Thạc sĩ</option>
                        <option value="Tiến sĩ">Tiến sĩ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_salary">Mức lương kỳ vọng (VNĐ)</label>
                    <input type="number" id="edit_salary" name="salary" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_status">Trạng thái</label>
                    <select id="edit_status" name="status" class="form-control" required>
                        <option value="Chưa tuyển">Chưa tuyển</option>
                        <option value="Đã tuyển">Đã tuyển</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editModal')">Hủy</button>
            <button class="btn btn-primary" onclick="validateEditForm()">Lưu thay đổi</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Xác nhận xóa</h3>
            <button class="close-btn" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body" style="height: 10vh;">
            <p>Bạn có chắc chắn muốn xóa ứng viên <span id="delete_name"></span>?</p>
            <form id="deleteForm" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="delete_id" name="id">
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Hủy</button>
            <button class="btn btn-danger" onclick="document.getElementById('deleteForm').submit()">Xóa</button>
        </div>
    </div>
</div>

<script>
    // Modal functions
    function openAddModal() {
        document.getElementById('addModal').classList.add('show');
    }

    function openEditModal(id, name, email, education_level, salary, status) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_education_level').value = education_level;
        document.getElementById('edit_salary').value = salary;
        document.getElementById('edit_status').value = status;
        document.getElementById('editModal').classList.add('show');
    }

    function confirmDelete(id, name) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_name').textContent = name;
        document.getElementById('deleteModal').classList.add('show');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    // search functionality
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
    function validateAddForm() {
        const form = document.getElementById('addForm');
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

    function validateEditForm() {
        const form = document.getElementById('editForm');
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