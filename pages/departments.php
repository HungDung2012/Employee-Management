<div class="departments-container">
    <div class="tab-nav">
        <a href="#" class="active" onclick="switchDeptTab('statistics')">Thống kê</a>
        <a href="#" onclick="switchDeptTab('management')">Quản lý</a>
    </div>
    
    <div id="statistics-tab" class="tab-content active">
        <div class="card-departments">
            <div class="card-header">
                <h2 class="card-title">Thống kê phòng ban</h2>
            </div>
            <div class="chart-container">
                <canvas id="departmentStatsChart"></canvas>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Phòng ban</th>
                        <th>Trưởng phòng</th>
                        <th>Ngày nhận chức</th>
                        <th>Số nhân viên</th>
                        <th>Lương trung bình</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch department data
                    $stmt = $conn->prepare("SELECT * FROM Department ORDER BY id");
                    $stmt->execute();
                    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($departments as $dept) {
                        echo "<tr>";
                        echo "<td>{$dept['id']}</td>";
                        echo "<td>{$dept['Ten_phong_ban']}</td>";
                        echo "<td>{$dept['Truong_phong']}</td>";
                        echo "<td>" . date('d/m/Y', strtotime($dept['Ngay_nhan_chuc'])) . "</td>";
                        echo "<td>{$dept['So_nhan_vien']}</td>";
                        echo "<td>" . number_format($dept['Luong_trung_binh'], 0, ',', '.') . " VNĐ</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="management-tab" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Quản lý phòng ban</h2>
                <button class="btn btn-primary" onclick="openAddDepartmentModal()">Thêm phòng ban mới</button>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên phòng ban</th>
                        <th>Trưởng phòng</th>
                        <th>Ngày nhận chức</th>
                        <th>Số nhân viên</th>
                        <th>Lương trung bình</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($departments as $dept) {
                        echo "<tr>";
                        echo "<td>{$dept['id']}</td>";
                        echo "<td>{$dept['Ten_phong_ban']}</td>";
                        echo "<td>{$dept['Truong_phong']}</td>";
                        echo "<td>" . date('d/m/Y', strtotime($dept['Ngay_nhan_chuc'])) . "</td>";
                        echo "<td>{$dept['So_nhan_vien']}</td>";
                        echo "<td>" . number_format($dept['Luong_trung_binh'], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>
                                <button class='btn btn-warning btn-sm' onclick='openEditDepartmentModal({$dept['id']})'>Sửa</button>
                                <button class='btn btn-danger btn-sm' onclick='confirmDeleteDepartment({$dept['id']}, \"{$dept['Ten_phong_ban']}\")'>Xóa</button>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal" id="addDepartmentModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Thêm phòng ban mới</h3>
            <button class="close-btn" onclick="closeModal('addDepartmentModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addDepartmentForm" method="POST">
                <input type="hidden" name="action" value="add_department">
                <div class="form-group">
                    <label for="dept_name">Tên phòng ban</label>
                    <input type="text" id="dept_name" name="dept_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="dept_manager">Trưởng phòng</label>
                    <input type="text" id="dept_manager" name="dept_manager" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="dept_date">Ngày nhận chức</label>
                    <input type="date" id="dept_date" name="dept_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="dept_employees">Số nhân viên</label>
                    <input type="number" id="dept_employees" name="dept_employees" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="dept_salary">Lương trung bình (VNĐ)</label>
                    <input type="number" id="dept_salary" name="dept_salary" class="form-control" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('addDepartmentModal')">Hủy</button>
            <button class="btn btn-primary" onclick="document.getElementById('addDepartmentForm').submit()">Lưu</button>
        </div>
    </div>
</div>

<script>
// Tab switching
function switchDeptTab(tab) {
    const tabs = document.querySelectorAll('.tab-content');
    const navLinks = document.querySelectorAll('.tab-nav a');
    
    tabs.forEach(t => t.classList.remove('active'));
    navLinks.forEach(link => link.classList.remove('active'));
    
    document.getElementById(tab + '-tab').classList.add('active');
    document.querySelector(`.tab-nav a[onclick="switchDeptTab('${tab}')"]`).classList.add('active');
    
    return false; // Prevent default link behavior
}

function openAddDepartmentModal() {
    document.getElementById('addDepartmentModal').classList.add('show');
}

function openEditDepartmentModal(id) {
    // In a real application, you would fetch the department data by ID
    alert('Chức năng sửa phòng ban ID: ' + id);
}

function confirmDeleteDepartment(id, name) {
    if (confirm('Bạn có chắc chắn muốn xóa phòng ban ' + name + '?')) {
        // In a real application, you would send a delete request
        alert('Đã xóa phòng ban: ' + name);
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// Department statistics chart
const deptStatsCtx = document.getElementById('departmentStatsChart').getContext('2d');
const deptStatsChart = new Chart(deptStatsCtx, {
    type: 'bar',
    data: {
        labels: ['2019', '2020', '2021', '2022', '2023'],
        datasets: [
            {
                label: 'Số nhân viên',
                data: [45, 52, 60, 65, 63],
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            },
            {
                label: 'Lương trung bình (triệu VNĐ)',
                data: [15, 16.5, 18, 20, 22],
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Số nhân viên'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
                title: {
                    display: true,
                    text: 'Lương trung bình (triệu VNĐ)'
                }
            }
        },
        plugins: {
            title: {
                display: true,
                text: 'Biến động nhân sự và lương theo năm'
            }
        }
    }
});
</script>
