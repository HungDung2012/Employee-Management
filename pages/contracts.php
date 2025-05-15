<div class="contracts-container">
    <div class="tab-nav">
        <a href="#" class="active" onclick="switchTab('contracts')">Hợp đồng</a>
        <a href="#" onclick="switchTab('statistics')">Thống kê</a>
    </div>
    
    <div id="contracts-tab" class="tab-content active">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Quản lý hợp đồng</h2>
                <button class="btn btn-primary" onclick="openAddContractModal()">Thêm hợp đồng mới</button>
            </div>
            
            <div class="filter-section">
                <div class="filter-item">
                    <label for="contract-search">Tìm kiếm</label>
                    <input type="text" id="contract-search" placeholder="Tên nhân viên...">
                </div>
                <div class="filter-item">
                    <label for="contract-department">Phòng ban</label>
                    <select id="contract-department">
                        <option value="">Tất cả</option>
                        <?php
                        // Fetch departments for filter
                        $stmt = $conn->prepare("SELECT DISTINCT Ten_phong_ban FROM Department ORDER BY Ten_phong_ban");
                        $stmt->execute();
                        $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($departments as $dept) {
                            echo "<option value='{$dept['Ten_phong_ban']}'>{$dept['Ten_phong_ban']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="contract-type">Loại hợp đồng</label>
                    <select id="contract-type">
                        <option value="">Tất cả</option>
                        <option value="Có thời hạn">Có thời hạn</option>
                        <option value="Không thời hạn">Không thời hạn</option>
                    </select>
                </div>
                <div class="filter-item" style="align-self: flex-end;">
                    <button class="btn btn-primary" onclick="applyContractFilters()">Lọc</button>
                </div>
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên nhân viên</th>
                        <th>Phòng ban</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Loại hợp đồng</th>
                        <th>Lương cơ bản</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch contract data
                    $stmt = $conn->prepare("SELECT id, Ten_nhan_vien, Phong_ban, Ngay_bat_dau_hop_dong, Ngay_ket_thuc_hop_dong, Loai_hop_dong, Luong_co_ban FROM Employee ORDER BY id");
                    $stmt->execute();
                    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($contracts as $contract) {
                        echo "<tr>";
                        echo "<td>{$contract['id']}</td>";
                        echo "<td>{$contract['Ten_nhan_vien']}</td>";
                        echo "<td>{$contract['Phong_ban']}</td>";
                        echo "<td>" . date('d/m/Y', strtotime($contract['Ngay_bat_dau_hop_dong'])) . "</td>";
                        
                        // Check if end date exists
                        if ($contract['Ngay_ket_thuc_hop_dong']) {
                            echo "<td>" . date('d/m/Y', strtotime($contract['Ngay_ket_thuc_hop_dong'])) . "</td>";
                        } else {
                            echo "<td>-</td>";
                        }
                        
                        echo "<td>{$contract['Loai_hop_dong']}</td>";
                        echo "<td>" . number_format($contract['Luong_co_ban'], 0, ',', '.') . " VNĐ</td>";
                        echo "<td>
                                <button class='btn btn-warning btn-sm' onclick='openEditContractModal({$contract['id']})'>Sửa</button>
                                <button class='btn btn-danger btn-sm' onclick='confirmDeleteContract({$contract['id']}, \"{$contract['Ten_nhan_vien']}\")'>Xóa</button>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="statistics-tab" class="tab-content">
        <div class="card-departments">
            <div class="card-header">
                <h2 class="card-title">Thống kê hợp đồng</h2>
            </div>
            <div class="chart-container">
                <canvas id="contractChart"></canvas>
            </div>
            <div class="card-body">
                <div class="stats-summary">
                    <div class="stat-item">
                        <h3>Tổng số hợp đồng: </h3>
                        <p class="stat-value"><?php echo count($contracts); ?></p>
                    </div>
                    <div class="stat-item">
                        <h3>Hợp đồng có thời hạn: </h3>
                        <p class="stat-value">
                            <?php
                            $count = 0;
                            foreach ($contracts as $contract) {
                                if ($contract['Loai_hop_dong'] == 'Có thời hạn') $count++;
                            }
                            echo $count;
                            ?>
                        </p>
                    </div>
                    <div class="stat-item">
                        <h3>Hợp đồng không thời hạn: </h3>
                        <p class="stat-value">
                            <?php
                            $count = 0;
                            foreach ($contracts as $contract) {
                                if ($contract['Loai_hop_dong'] == 'Không thời hạn') $count++;
                            }
                            echo $count;
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Contract Modal (simplified for example) -->
<div class="modal" id="addContractModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Thêm hợp đồng mới</h3>
            <button class="close-btn" onclick="closeModal('addContractModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addContractForm" method="POST">
                <input type="hidden" name="action" value="add_contract">
                <div class="form-group">
                    <label for="contract_employee">Nhân viên</label>
                    <select id="contract_employee" name="contract_employee" class="form-control" required>
                        <option value="">Chọn nhân viên</option>
                        <?php
                        // In a real application, you would fetch employees without contracts
                        echo "<option value='1'>Nguyễn Văn A</option>";
                        echo "<option value='2'>Trần Thị B</option>";
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="contract_department">Phòng ban</label>
                    <select id="contract_department" name="contract_department" class="form-control" required>
                        <?php
                        foreach ($departments as $dept) {
                            echo "<option value='{$dept['Ten_phong_ban']}'>{$dept['Ten_phong_ban']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="contract_start_date">Ngày bắt đầu</label>
                    <input type="date" id="contract_start_date" name="contract_start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="contract_end_date">Ngày kết thúc</label>
                    <input type="date" id="contract_end_date" name="contract_end_date" class="form-control">
                </div>
                <div class="form-group">
                    <label for="contract_type">Loại hợp đồng</label>
                    <select id="contract_type" name="contract_type" class="form-control" required>
                        <option value="Có thời hạn">Có thời hạn</option>
                        <option value="Không thời hạn">Không thời hạn</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="contract_salary">Lương cơ bản (VNĐ)</label>
                    <input type="number" id="contract_salary" name="contract_salary" class="form-control" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('addContractModal')">Hủy</button>
            <button class="btn btn-primary" onclick="document.getElementById('addContractForm').submit()">Lưu</button>
        </div>
    </div>
</div>

<script>
// Tab switching
function switchTab(tab) {
    const tabs = document.querySelectorAll('.tab-content');
    const navLinks = document.querySelectorAll('.tab-nav a');
    
    tabs.forEach(t => t.classList.remove('active'));
    navLinks.forEach(link => link.classList.remove('active'));
    
    document.getElementById(tab + '-tab').classList.add('active');
    document.querySelector(`.tab-nav a[onclick="switchTab('${tab}')"]`).classList.add('active');
    
    return false; // Prevent default link behavior
}

function openAddContractModal() {
    document.getElementById('addContractModal').classList.add('show');
}

function openEditContractModal(id) {
    // In a real application, you would fetch the contract data by ID
    alert('Chức năng sửa hợp đồng ID: ' + id);
}

function confirmDeleteContract(id, name) {
    if (confirm('Bạn có chắc chắn muốn xóa hợp đồng của ' + name + '?')) {
        // In a real application, you would send a delete request
        alert('Đã xóa hợp đồng của: ' + name);
    }
}

function applyContractFilters() {
    // In a real application, this would filter the table or reload with filtered data
    alert('Đã áp dụng bộ lọc hợp đồng');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// Contract statistics chart
const contractCtx = document.getElementById('contractChart').getContext('2d');
const contractChart = new Chart(contractCtx, {
    type: 'bar',
    data: {
        labels: ['2019', '2020', '2021', '2022', '2023'],
        datasets: [
            {
                label: 'Số lượng hợp đồng',
                data: [10, 15, 20, 18, 25],
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Số lượng hợp đồng'
                }
            }
        },
        plugins: {
            title: {
                display: true,
                text: 'Thống kê hợp đồng theo năm'
            }
        }
    }
});
</script>
