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
        // Delete attendance record
        if ($_POST['action'] === 'delete_attendance') {
            $id = $_POST['attendance_id'];

            try {
                // First delete related records in AttendanceDetail
                $stmt = $conn->prepare("DELETE FROM AttendanceDetail WHERE attendance_id = ?");
                $stmt->execute([$id]);

                // Then delete the attendance record
                $stmt = $conn->prepare("DELETE FROM Attendance WHERE id = ?");
                $stmt->execute([$id]);

                $success_message = "Đã xóa bản ghi chấm công có ID: " . $id;
            } catch (PDOException $e) {
                $error_message = "Lỗi khi xóa bản ghi chấm công: " . $e->getMessage();
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

// Filter by department
if (isset($_GET['department']) && !empty($_GET['department'])) {
    $where_conditions[] = "e.Phong_ban = ?";
    $params[] = $_GET['department'];
}

// Filter by month
if (isset($_GET['month']) && !empty($_GET['month'])) {
    $where_conditions[] = "a.Thang = ?";
    $params[] = $_GET['month'];
}

// Filter by year
if (isset($_GET['year']) && !empty($_GET['year'])) {
    $where_conditions[] = "a.Nam = ?";
    $params[] = $_GET['year'];
}

$where_clause = "";
if (!empty($where_conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Build order by
$order_by = "ORDER BY a.id DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'name_asc':
            $order_by = "ORDER BY e.Ten_nhan_vien ASC";
            break;
        case 'name_desc':
            $order_by = "ORDER BY e.Ten_nhan_vien DESC";
            break;
        case 'date_asc':
            $order_by = "ORDER BY a.Thoi_gian_cham_cong ASC";
            break;
        case 'date_desc':
            $order_by = "ORDER BY a.Thoi_gian_cham_cong DESC";
            break;
    }
}

// Fetch departments for filter
$stmt = $conn->prepare("SELECT DISTINCT Ten_phong_ban FROM Department ORDER BY Ten_phong_ban");
$stmt->execute();
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="attendance-container">
    <div class="card">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Quản lý Chấm công</h2>
                <button class="btn btn-primary" onclick="openAttendanceModal()">Chấm công</button>
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

            <!-- Search Bar and Filters -->
            <div class="filter-section">
                <form method="GET" id="attendanceFilterForm" style="display: flex; width: 100%;">
                    <input type="hidden" name="page" value="attendance">

                    <div class="filter-item" style="width:40%;flex: none;">
                        <label for="search">Tìm kiếm</label>
                        <input type="text" id="search" name="search" placeholder="Tên nhân viên..."
                            class="form-control" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>

                    <div class="filter-item">
                        <label for="year">Năm</label>
                        <select id="year" name="year" class="form-control">
                            <option value="">Tất cả</option>
                            <?php
                            $currentYear = date('Y');
                            for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                                $selected = (isset($_GET['year']) && $_GET['year'] == $i) ? 'selected' : '';
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filter-item">
                        <label for="month">Tháng</label>
                        <select id="month" name="month" class="form-control">
                            <option value="">Tất cả</option>
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                $selected = (isset($_GET['month']) && $_GET['month'] == $i) ? 'selected' : '';
                                echo "<option value='$i' $selected>Tháng $i</option>";
                            }
                            ?>
                        </select>
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
                        <label for="sort">Sắp xếp</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="date_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'date_desc') ? 'selected' : ''; ?>>Ngày mới nhất</option>
                            <option value="date_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'date_asc') ? 'selected' : ''; ?>>Ngày cũ nhất</option>
                            <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Tên (A-Z)</option>
                            <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Tên (Z-A)</option>
                        </select>
                    </div>

                    <div class="filter-item" style="align-items: flex-end;flex-direction: row;margin-left: 5px;">
                        <button type="submit" style="margin-right: 5px;" class="btn btn-primary">Lọc</button>
                        <button type="button" class="btn btn-secondary">
                            <a href="index.php?page=attendance" style="text-decoration: none;color: white;">
                                Đặt lại
                            </a>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Attendance Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên nhân viên</th>
                        <th>Phòng ban</th>
                        <th>Thời gian chấm công</th>
                        <th>Số ngày làm việc</th>
                        <th>Số ngày nghỉ</th>
                        <th>Số ngày trễ</th>
                        <th>Giờ làm thêm</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch attendance data with filters
                    $sql = "SELECT a.id, e.Ten_nhan_vien, e.Phong_ban, a.Thoi_gian_cham_cong, 
                               a.So_ngay_lam_viec, a.So_ngay_nghi, a.So_ngay_tre, a.Gio_lam_them,
                               a.Thang, a.Nam
                        FROM Attendance a 
                        JOIN Employee e ON a.employee_id = e.id 
                        $where_clause $order_by";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    $attendances = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($attendances) > 0) {
                        foreach ($attendances as $attendance) {
                            echo "<tr>";
                            echo "<td>{$attendance['id']}</td>";
                            echo "<td>{$attendance['Ten_nhan_vien']}</td>";
                            echo "<td>{$attendance['Phong_ban']}</td>";
                            echo "<td>" . date('d/m/Y', strtotime($attendance['Thoi_gian_cham_cong'])) . "</td>";
                            echo "<td>{$attendance['So_ngay_lam_viec']}</td>";
                            echo "<td>{$attendance['So_ngay_nghi']}</td>";
                            echo "<td>{$attendance['So_ngay_tre']}</td>";
                            echo "<td>{$attendance['Gio_lam_them']} giờ</td>";
                            echo "<td>
                                <button class='btn btn-info btn-sm' onclick='viewAttendanceDetails({$attendance['id']})'>Chi tiết</button>
                                <button class='btn btn-danger btn-sm' onclick='confirmDeleteAttendance({$attendance['id']})'>Xóa</button>
                          </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' style='text-align: center;'>Không tìm thấy dữ liệu chấm công</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Attendance Modal -->
    <div class="modal" id="attendanceModal">
        <div class="modal-content attendance-modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Chấm công nhân viên</h3>
                <button class="close-btn" onclick="closeModal('attendanceModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="attendance-filters">
                    <div class="filter-item">
                        <label for="attendance-month">Tháng</label>
                        <select id="attendance-month" class="form-control" onchange="updateAttendanceCalendar()">
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                $selected = ($i == $currentMonth) ? 'selected' : '';
                                echo "<option value='$i' $selected>Tháng $i</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="attendance-year">Năm</label>
                        <select id="attendance-year" class="form-control" onchange="updateAttendanceCalendar()">
                            <?php
                            for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                                $selected = ($i == $currentYear) ? 'selected' : '';
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="attendance-content">
                    <div class="employee-list">
                        <h4>Danh sách nhân viên</h4>
                        <div class="employee-search">
                            <input type="text" id="employee-search" placeholder="Tìm nhân viên..." class="form-control">

                        </div>
                        <div class="employee-table-container">
                            <table class="employee-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên nhân viên</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody id="employee-list-body">
                                    <?php
                                    // Fetch employees
                                    $stmt = $conn->prepare("SELECT id, Ten_nhan_vien FROM Employee ORDER BY id");
                                    $stmt->execute();
                                    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($employees as $employee) {
                                        // Check if employee has attendance record for current month
                                        $stmt = $conn->prepare("SELECT COUNT(*) FROM Attendance 
                                                          WHERE employee_id = :employee_id 
                                                          AND MONTH(Thoi_gian_cham_cong) = :month 
                                                          AND YEAR(Thoi_gian_cham_cong) = :year");
                                        $stmt->bindParam(':employee_id', $employee['id'], PDO::PARAM_INT);
                                        $stmt->bindParam(':month', $currentMonth, PDO::PARAM_INT);
                                        $stmt->bindParam(':year', $currentYear, PDO::PARAM_INT);
                                        $stmt->execute();
                                        $hasAttendance = (int)$stmt->fetchColumn() > 0;

                                        $statusClass = $hasAttendance ? 'status-recorded' : 'status-not-recorded';
                                        $statusText = $hasAttendance ? 'Đã chấm công' : 'Chưa chấm công';

                                        echo "<tr class='emp-name' onclick='selectEmployee({$employee['id']}, \"{$employee['Ten_nhan_vien']}\")'>";
                                        echo "<td>{$employee['id']}</td>";
                                        echo "<td>{$employee['Ten_nhan_vien']}</td>";
                                        echo "<td><span class='status-badge $statusClass'>$statusText</span></td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="attendance-calendar" id="attendance-calendar">
                        <h4>Chấm công cho: <span id="selected-employee-name">Chưa chọn</span></h4>

                        <div class="attendance-actions">
                            <button class="btn btn-danger" onclick="setAttendanceStatus('off')">Nghỉ</button>
                            <button class="btn btn-warning" onclick="setAttendanceStatus('late')">Đi trễ</button>
                            <div class="dropdown">
                                <button class="btn btn-success" onclick="toggleOvertimeOptions()">Tăng ca</button>
                                <div class="dropdown-content" id="overtime-options">
                                    <a href="#" onclick="setOvertimeHours(1); return false;">1 giờ</a>
                                    <a href="#" onclick="setOvertimeHours(2); return false;">2 giờ</a>
                                    <a href="#" onclick="setOvertimeHours(4); return false;">4 giờ</a>
                                    <a href="#" onclick="setOvertimeHours(8); return false;">8 giờ</a>
                                </div>
                            </div>
                            <button class="btn btn-secondary" onclick="clearAttendanceStatus()">Xóa</button>
                        </div>

                        <div class="calendar-container" id="calendar-container">

                            
                            <p class="select-employee-message">Vui lòng chọn nhân viên từ danh sách bên trái</p>
                        </div>

                        <div class="attendance-summary" id="attendance-summary" style="display: none;">
                            <h4>Tổng kết</h4>
                            <div class="summary-item">
                                <span>Số ngày làm việc:</span>
                                <span id="summary-working-days">0</span>
                            </div>
                            <div class="summary-item">
                                <span>Số ngày nghỉ:</span>
                                <span id="summary-off-days">0</span>
                            </div>
                            <div class="summary-item">
                                <span>Số ngày đi trễ:</span>
                                <span id="summary-late-days">0</span>
                            </div>
                            <div class="summary-item">
                                <span>Giờ làm thêm:</span>
                                <span id="summary-overtime-hours">0</span>
                            </div>
                        </div>

                        <div class="attendance-save">
                            <button class="btn btn-primary" onclick="saveAttendance()">Lưu chấm công</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Details Modal -->
    <div class="modal" id="attendanceDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Chi tiết chấm công</h3>
                <button class="close-btn" onclick="closeModal('attendanceDetailsModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div id="attendance-details-content">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('attendanceDetailsModal')">Đóng</button>
                <button class="btn btn-primary" onclick="printAttendanceDetails()">In báo cáo</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteAttendanceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Xác nhận xóa</h3>
                <button class="close-btn" onclick="closeModal('deleteAttendanceModal')">&times;</button>
            </div>
            <div class="modal-body" style="height: 7vh;">
                <p>Bạn có chắc chắn muốn xóa bản ghi chấm công này?</p>
                <form id="deleteAttendanceForm" method="POST">
                    <input type="hidden" name="action" value="delete_attendance">
                    <input type="hidden" id="delete_attendance_id" name="attendance_id">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('deleteAttendanceModal')">Hủy</button>
                <button class="btn btn-danger" onclick="document.getElementById('deleteAttendanceForm').submit()">Xóa</button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let selectedEmployeeId = null;
        let selectedEmployeeName = '';
        let currentAttendanceStatus = 'normal'; // normal, off, late
        let currentOvertimeHours = 0;
        let attendanceData = {}; // Format: { day: { status: 'normal|off|late', overtime: 0 } }

        // Open attendance modal
        function openAttendanceModal() {
            document.getElementById('attendanceModal').classList.add('show');
            updateAttendanceCalendar();
        }

        // search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.attendance-search');
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

        // Update attendance calendar based on selected month and year
        function updateAttendanceCalendar() {
            if (!selectedEmployeeId) {
                document.getElementById('calendar-container').innerHTML = '<p class="select-employee-message">Vui lòng chọn nhân viên từ danh sách bên trái</p>';
                return;
            }

            const month = parseInt(document.getElementById('attendance-month').value);
            const year = parseInt(document.getElementById('attendance-year').value);

            // Get number of days in the selected month
            const daysInMonth = new Date(year, month, 0).getDate();

            // Create calendar
            let calendarHTML = '<table class="calendar-table">';
            calendarHTML += '<thead><tr>';
            calendarHTML += '<th>CN</th><th>T2</th><th>T3</th><th>T4</th><th>T5</th><th>T6</th><th>T7</th>';
            calendarHTML += '</tr></thead>';
            calendarHTML += '<tbody>';

            // Get the first day of the month (0 = Sunday, 1 = Monday, etc.)
            const firstDay = new Date(year, month - 1, 1).getDay();

            let dayCount = 1;

            // Create calendar rows
            for (let i = 0; i < 6; i++) {
                calendarHTML += '<tr>';

                // Create calendar cells
                for (let j = 0; j < 7; j++) {
                    if ((i === 0 && j < firstDay) || dayCount > daysInMonth) {
                        // Empty cell
                        calendarHTML += '<td></td>';
                    } else {
                        // Day cell
                        const dayKey = dayCount.toString();
                        const dayData = attendanceData[dayKey] || {
                            status: 'normal',
                            overtime: 0
                        };
                        const cellClass = `calendar-day status-${dayData.status}`;
                        const overtimeText = dayData.overtime > 0 ? `<div class="overtime-indicator">+${dayData.overtime}h</div>` : '';

                        calendarHTML += `<td class="${cellClass}" data-day="${dayCount}" onclick="toggleDayStatus(${dayCount})">${dayCount}${overtimeText}</td>`;
                        dayCount++;
                    }
                }

                calendarHTML += '</tr>';

                // Stop if we've displayed all days
                if (dayCount > daysInMonth) {
                    break;
                }
            }

            calendarHTML += '</tbody></table>';

            document.getElementById('calendar-container').innerHTML = calendarHTML;
            updateAttendanceSummary();
        }

        // Select employee for attendance
        function selectEmployee(employeeId, employeeName) {
            selectedEmployeeId = employeeId;
            selectedEmployeeName = employeeName;
            document.getElementById('selected-employee-name').textContent = employeeName;

            // Reset attendance data
            attendanceData = {};

            // Show attendance summary
            document.getElementById('attendance-summary').style.display = 'block';

            // Load existing attendance data if available
            loadEmployeeAttendance(employeeId);

            // Update calendar
            updateAttendanceCalendar();
        }

        // Load employee attendance data
        function loadEmployeeAttendance(employeeId) {
            const month = parseInt(document.getElementById('attendance-month').value);
            const year = parseInt(document.getElementById('attendance-year').value);

            // Reset attendance data
            attendanceData = {};

            // Get number of days in the selected month
            const daysInMonth = new Date(year, month, 0).getDate();

            // Initialize all working days (Monday to Friday) with normal status
            for (let day = 1; day <= daysInMonth; day++) {
                // Create date object for this day
                const date = new Date(year, month - 1, day);

                // Get day of week (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
                const dayOfWeek = date.getDay();
                if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                    attendanceData[day.toString()] = {
                        status: 'normal',
                        overtime: 0
                    };
                }
            }
        }

        // Toggle day status (normal -> off -> late -> normal)
        function toggleDayStatus(day) {
            const dayKey = day.toString();

            if (!attendanceData[dayKey]) {
                attendanceData[dayKey] = {
                    status: 'normal',
                    overtime: 0
                };
            }

            // Apply selected status
            if (currentAttendanceStatus !== 'normal') {
                attendanceData[dayKey].status = currentAttendanceStatus;
            } else {
                // If no status is selected, cycle through statuses
                switch (attendanceData[dayKey].status) {
                    case 'normal':
                        attendanceData[dayKey].status = 'off';
                        break;
                    case 'off':
                        attendanceData[dayKey].status = 'late';
                        break;
                    case 'late':
                        attendanceData[dayKey].status = 'normal';
                        break;
                }
            }

            // Apply overtime hours if set
            if (currentOvertimeHours > 0 && attendanceData[dayKey].status === 'normal') {
                attendanceData[dayKey].overtime = currentOvertimeHours;
            }

            updateAttendanceCalendar();
        }

        // Set attendance status for selected days
        function setAttendanceStatus(status) {
            currentAttendanceStatus = status;
            currentOvertimeHours = 0; // Reset overtime hours

            // Update UI to show active status
            document.querySelectorAll('.attendance-actions .btn').forEach(btn => {
                btn.classList.remove('active');
            });

            if (status === 'off') {
                document.querySelectorAll('.attendance-actions .btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelector('.attendance-actions .btn-warning').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelector('.attendance-actions .btn-danger').classList.add('active');

            } else if (status === 'late') {
                document.querySelector('.attendance-actions .btn-danger').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('.attendance-actions .btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelector('.attendance-actions .btn-warning').classList.add('active');

            }
        }

        // Toggle overtime options dropdown
        function toggleOvertimeOptions() {
            document.getElementById('overtime-options').classList.toggle('show');
        }

        // Set overtime hours
        function setOvertimeHours(hours) {
            currentOvertimeHours = hours;
            currentAttendanceStatus = 'normal'; // Reset status to normal

            document.querySelectorAll('.attendance-actions .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector('.attendance-actions .btn-warning').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.attendance-actions .btn-danger').forEach(btn => {
                btn.classList.remove('active');
            });
            // Update UI
            document.querySelectorAll('.attendance-actions .btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector('.attendance-actions .btn-success').classList.add('active');
            document.getElementById('overtime-options').classList.remove('show');
        }

        // Clear attendance status
        function clearAttendanceStatus() {
            currentAttendanceStatus = 'normal';
            currentOvertimeHours = 0;

            // Update UI
            let cnt = document.querySelectorAll('.attendance-actions .btn').forEach(btn => {
                btn.classList.removeAll('active');
            });
        }

        // Update attendance summary
        function updateAttendanceSummary() {
            let workingDays = 0;
            let offDays = 0;
            let lateDays = 0;
            let overtimeHours = 0;

            // Calculate summary
            for (const day in attendanceData) {
                const data = attendanceData[day];

                if (data.status === 'normal') {
                    workingDays++;
                    overtimeHours += data.overtime;
                } else if (data.status === 'off') {
                    offDays++;
                } else if (data.status === 'late') {
                    lateDays++;
                    workingDays++; // Late days are also working days
                }
            }

            // Update summary display
            document.getElementById('summary-working-days').textContent = workingDays;
            document.getElementById('summary-off-days').textContent = offDays;
            document.getElementById('summary-late-days').textContent = lateDays;
            document.getElementById('summary-overtime-hours').textContent = overtimeHours;
        }

        // Save attendance data
        function saveAttendance() {
            if (!selectedEmployeeId) {
                alert('Vui lòng chọn nhân viên trước khi lưu.');
                return;
            }

            const month = parseInt(document.getElementById('attendance-month').value);
            const year = parseInt(document.getElementById('attendance-year').value);

            // Calculate summary
            let workingDays = 0;
            let offDays = 0;
            let lateDays = 0;
            let overtimeHours = 0;

            for (const day in attendanceData) {
                const data = attendanceData[day];

                if (data.status === 'normal') {
                    workingDays++;
                    overtimeHours += data.overtime;
                } else if (data.status === 'off') {
                    offDays++;
                } else if (data.status === 'late') {
                    lateDays++;
                    workingDays++; // Late days are also working days
                }
            }

            // Prepare data to send to server
            const attendanceRecord = {
                employee_id: selectedEmployeeId,
                month: month,
                year: year,
                working_days: workingDays,
                off_days: offDays,
                late_days: lateDays,
                overtime_hours: overtimeHours,
                attendance_details: attendanceData
            };

            // Send request to save attendance
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/save_attendance.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');

            xhr.onload = function() {
                if (this.status === 200) {
                    const response = JSON.parse(this.responseText);

                    if (response.success) {
                        alert(`Đã lưu chấm công cho nhân viên ${selectedEmployeeName}:\n` +
                            `- Số ngày làm việc: ${workingDays}\n` +
                            `- Số ngày nghỉ: ${offDays}\n` +
                            `- Số ngày trễ: ${lateDays}\n` +
                            `- Giờ làm thêm: ${overtimeHours}`);

                        refreshEmployeeList();

                        location.reload();

                        closeModal('attendanceModal');
                    } else {
                        alert('Lỗi khi lưu chấm công: ' + response.message);
                    }

                } else {
                    alert('Lỗi kết nối server: ' + this.status);
                }
            };

            xhr.send(JSON.stringify(attendanceRecord));
        }

        // Refresh employee list
        function refreshEmployeeList() {
            // In a real application, you would fetch updated employee list from the server
            // For this example, we'll just update the status of the selected employee
            if (selectedEmployeeId) {
                const employeeRows = document.querySelectorAll('#employee-list-body tr');

                employeeRows.forEach(row => {
                    const rowId = parseInt(row.cells[0].textContent);

                    if (rowId === selectedEmployeeId) {
                        const statusCell = row.cells[2];
                        statusCell.innerHTML = '<span class="status-badge status-recorded">Đã chấm công</span>';
                    }
                });
            }
        }

        // View attendance details
        function viewAttendanceDetails(id) {
            // In a real application, you would fetch attendance details by ID
            // For this example, we'll use dummy data
            document.getElementById('attendanceDetailsModal').classList.add('show');
            document.getElementById('attendance-details-content').innerHTML = '<div class="loading-spinner">Đang tải dữ liệu...</div>';

            // Fetch attendance details using AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'ajax/get_attendance_details.php?id=' + id, true);

            xhr.onload = function() {
                if (this.status === 200) {
                    try {
                        const response = JSON.parse(this.responseText);

                        if (response.success) {
                            const attendance = response.attendance;
                            const details = response.details;
                            const employee = response.employee;

                            // Get month and year from attendance record
                            const month = attendance.Thang;
                            const year = attendance.Nam;

                            // Create calendar view
                            let calendarHTML = createCalendarView(month, year, details);

                            // Create attendance details HTML
                            let detailsHTML = `
                        <div class="attendance-details">
                            <h4>CHI TIẾT CHẤM CÔNG</h4>
                            <p><strong>Nhân viên:</strong> ${employee.Ten_nhan_vien}</p>
                            <p><strong>Phòng ban:</strong> ${employee.Phong_ban}</p>
                            <p><strong>Thời gian:</strong> Tháng ${month}/${year}</p>
                            <p><strong>Người chấm công:</strong> ${attendance.Nguoi_cham_cong}</p>
                            <hr>
                            <div class="calendar-view">
                                ${calendarHTML}
                            </div>
                            <div class="attendance-legend">
                                <div class="legend-item"><span class="legend-color normal"></span> Đi làm</div>
                                <div class="legend-item"><span class="legend-color off"></span> Nghỉ</div>
                                <div class="legend-item"><span class="legend-color late"></span> Đi trễ</div>
                                <div class="legend-item"><span class="overtime-indicator-small">+2h</span> Tăng ca</div>
                            </div>
                            <div class="attendance-summary-details">
                                <h5>Tổng kết</h5>
                                <div class="summary-item">
                                    <span>Số ngày làm việc:</span>
                                    <span>${attendance.So_ngay_lam_viec}</span>
                                </div>
                                <div class="summary-item">
                                    <span>Số ngày nghỉ:</span>
                                    <span>${attendance.So_ngay_nghi}</span>
                                </div>
                                <div class="summary-item">
                                    <span>Số ngày đi trễ:</span>
                                    <span>${attendance.So_ngay_tre}</span>
                                </div>
                                <div class="summary-item">
                                    <span>Giờ làm thêm:</span>
                                    <span>${attendance.Gio_lam_them}</span>
                                </div>
                            </div>
                            ${attendance.Ghi_chu ? `
                            <div class="attendance-notes">
                                <h5>Ghi chú</h5>
                                <p>${attendance.Ghi_chu}</p>
                            </div>` : ''}
                        </div>
                    `;

                            document.getElementById('attendance-details-content').innerHTML = detailsHTML;
                        } else {
                            document.getElementById('attendance-details-content').innerHTML = `
                        <div class="error-message">
                            <p>Không thể tải dữ liệu chấm công: ${response.message}</p>
                        </div>
                    `;
                        }
                    } catch (e) {
                        document.getElementById('attendance-details-content').innerHTML = `
                    <div class="error-message">
                        <p>Lỗi khi xử lý dữ liệu: ${e.message}</p>
                    </div>
                `;
                    }
                } else {
                    document.getElementById('attendance-details-content').innerHTML = `
                <div class="error-message">
                    <p>Lỗi khi tải dữ liệu: ${this.status}</p>
                </div>
            `;
                }
            };

            xhr.onerror = function() {
                document.getElementById('attendance-details-content').innerHTML = `
            <div class="error-message">
                <p>Lỗi kết nối máy chủ</p>
            </div>
        `;
            };

            xhr.send();
        }

        // Confirm delete attendance
        function confirmDeleteAttendance(id) {
            document.getElementById('delete_attendance_id').value = id;
            document.getElementById('deleteAttendanceModal').classList.add('show');
        }

        // Print attendance details
        function printAttendanceDetails() {
            const content = document.getElementById('attendance-details-content').innerHTML;
            const printWindow = window.open('', '_blank');

            printWindow.document.write(`
        <html>
        <head>
            <title>Chi tiết chấm công</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .attendance-details { max-width: 800px; margin: 0 auto; padding: 20px; }
                .calendar-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .calendar-table th, .calendar-table td { 
                    padding: 8px; 
                    text-align: center; 
                    border: 1px solid #ddd; 
                }
                .status-off { background-color: #ffcccc; }
                .status-late { background-color: #fff2cc; }
                .attendance-legend { 
                    display: flex; 
                    justify-content: space-around; 
                    margin: 20px 0; 
                }
                .legend-item { display: flex; align-items: center; }
                .legend-color { 
                    width: 20px; 
                    height: 20px; 
                    margin-right: 5px; 
                    border: 1px solid #ddd; 
                }
                .normal { background-color: white; }
                .off { background-color: #ffcccc; }
                .late { background-color: #fff2cc; }
                .overtime-indicator-small {
                    font-size: 12px;
                    color: green;
                    margin-right: 5px;
                }
                .attendance-summary-details { margin-top: 20px; }
                .summary-item { display: flex; justify-content: space-between; margin: 5px 0; }
            </style>
        </head>
        <body>
            <div class="attendance-details">
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

        // Reset attendance filters
        function resetAttendanceFilters() {
            document.getElementById('attendance-search').value = '';

            const currentYear = new Date().getFullYear();
            const currentMonth = new Date().getMonth() + 1;

            document.getElementById('filter-year').value = currentYear;
            document.getElementById('filter-month').value = currentMonth;
            document.getElementById('filter-department').value = '';

            // In a real application, you would reset the table to its default state
            alert('Đã đặt lại bộ lọc');
        }


        // Helper function to create calendar view
        function createCalendarView(month, year, details) {
            // Get number of days in the selected month
            const daysInMonth = new Date(year, month, 0).getDate();

            // Get the first day of the month (0 = Sunday, 1 = Monday, etc.)
            const firstDay = new Date(year, month - 1, 1).getDay();

            // Create calendar
            let calendarHTML = '<table class="calendar-table">';
            calendarHTML += '<thead><tr>';
            calendarHTML += '<th>CN</th><th>T2</th><th>T3</th><th>T4</th><th>T5</th><th>T6</th><th>T7</th>';
            calendarHTML += '</tr></thead>';
            calendarHTML += '<tbody>';

            // Create a map of attendance details by day
            const detailsByDay = {};
            details.forEach(detail => {
                const day = new Date(detail.Ngay).getDate();
                detailsByDay[day] = detail;
            });

            let dayCount = 1;

            // Create calendar rows
            for (let i = 0; i < 6; i++) {
                calendarHTML += '<tr>';

                // Create calendar cells
                for (let j = 0; j < 7; j++) {
                    if ((i === 0 && j < firstDay) || dayCount > daysInMonth) {
                        // Empty cell
                        calendarHTML += '<td></td>';
                    } else {
                        // Day cell
                        const detail = detailsByDay[dayCount];
                        let cellClass = '';
                        let overtimeText = '';

                        if (detail) {
                            // Map the status from database to CSS class
                            switch (detail.Trang_thai) {
                                case 'absent':
                                    cellClass = 'status-off';
                                    break;
                                case 'late':
                                    cellClass = 'status-late';
                                    break;
                                case 'overtime':
                                case 'normal':
                                    cellClass = 'status-normal';
                                    break;
                            }

                            // Add overtime indicator if applicable
                            if (detail.Gio_tang_ca > 0) {
                                overtimeText = `<div class="overtime-indicator">+${detail.Gio_tang_ca}h</div>`;
                            }

                            // Add tooltip with notes if available
                            const tooltip = detail.Ghi_chu ? ` title="${detail.Ghi_chu}"` : '';

                            calendarHTML += `<td class="calendar-day ${cellClass}"${tooltip}>${dayCount}${overtimeText}</td>`;
                        } else {
                            // No attendance record for this day
                            calendarHTML += `<td class="calendar-day">${dayCount}</td>`;
                        }

                        dayCount++;
                    }
                }

                calendarHTML += '</tr>';

                // Stop if we've displayed all days
                if (dayCount > daysInMonth) {
                    break;
                }
            }

            calendarHTML += '</tbody></table>';

            return calendarHTML;
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Close the dropdown when clicking outside of it
        window.onclick = function(event) {
            if (!event.target.matches('.btn-success')) {
                const dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>