<div class="dashboard-container">
    <div class="dashboard-card">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Thống kê nhân viên và lương trung bình theo năm</h2>
            </div>
            <div class="chart-container">
                <canvas id="employeeChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Tỷ lệ nhân viên theo phòng ban</h2>
            </div>
            <div class="chart-container">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Thông tin phòng ban</h2>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên phòng ban</th>
                    <th>Quản lý hiện tại</th>
                    <th>Ngày nhận chức</th>
                    <th>Số lượng nhân viên</th>
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

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
    // Data for charts
    <?php
    // Prepare data for department pie chart
    $stmt = $conn->prepare("SELECT Ten_phong_ban, So_nhan_vien FROM Department");
    $stmt->execute();
    $deptData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $deptLabels = [];
    $deptValues = [];
    $deptColors = [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(255, 159, 64, 0.7)',
        'rgba(199, 199, 199, 0.7)'
    ];

    foreach ($deptData as $index => $dept) {
        $deptLabels[] = $dept['Ten_phong_ban'];
        $deptValues[] = $dept['So_nhan_vien'];
    }

    // Sample data for employee and salary chart (in a real app, this would come from the database)
    $years = ['2019', '2020', '2021', '2022', '2023'];
    $employeeCounts = [45, 52, 60, 65, 63];
    $avgSalaries = [15000000, 16500000, 18000000, 20000000, 22000000];
    ?>

    // Department distribution chart
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    const departmentChart = new Chart(departmentCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($deptLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($deptValues); ?>,
                backgroundColor: <?php echo json_encode($deptColors); ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                title: {
                    display: true,
                    text: 'Phân bố nhân viên theo phòng ban'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            // const total = context.dataset.data.reduce((a, b) => a + b, 63);
                            const percentage = ((value / 63) * 100).toFixed(1);
                            return `${label}: ${value} người (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Employee and salary chart
    const employeeCtx = document.getElementById('employeeChart').getContext('2d');
    const employeeChart = new Chart(employeeCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($years); ?>,
            datasets: [{
                    label: 'Số lượng nhân viên',
                    data: <?php echo json_encode($employeeCounts); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Lương trung bình (triệu VNĐ)',
                    data: <?php echo json_encode(array_map(function ($salary) {
                                return $salary / 1000000;
                            }, $avgSalaries)); ?>,
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
                        text: 'Số lượng nhân viên'
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