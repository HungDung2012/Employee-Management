<aside class="sidebar">
    <div class="sidebar-header">
        <div class="admin-profile">
            <div class="admin-avatar">
                <img src="images/avatar.png" alt="Admin Avatar">
            </div>
            <div class="admin-info">
                <h3><?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin User'; ?></h3>
                <p><?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'Quản trị viên'; ?></p>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo ($page == 'dashboard') ? 'active' : ''; ?>">
                <a href="index.php?page=dashboard">
                    <i class="icon dashboard-icon"></i>
                    <span>Trang chủ</span>
                </a>
            </li>
            <li class="<?php echo ($page == 'recruitment') ? 'active' : ''; ?>">
                <a href="index.php?page=recruitment">
                    <i class="icon recruitment-icon"></i>
                    <span>Tuyển dụng</span>
                </a>
            </li>
            <li class="<?php echo ($page == 'employees') ? 'active' : ''; ?>">
                <a href="index.php?page=employees">
                    <i class="icon employees-icon"></i>
                    <span>Nhân viên</span>
                </a>
            </li>
            <li class="<?php echo ($page == 'contracts') ? 'active' : ''; ?>">
                <a href="index.php?page=contracts">
                    <i class="icon contracts-icon"></i>
                    <span>Hợp đồng</span>
                </a>
            </li>
            <li class="<?php echo ($page == 'departments') ? 'active' : ''; ?>">
                <a href="index.php?page=departments">
                    <i class="icon departments-icon"></i>
                    <span>Phòng ban</span>
                </a>
            </li>
            <li class="<?php echo ($page == 'attendance') ? 'active' : ''; ?>">
              <a href="index.php?page=attendance">
                  <i class="icon attendance-icon"></i>
                  <span>Chấm công</span>
              </a>
          </li>
            <li class="<?php echo ($page == 'salary') ? 'active' : ''; ?>">
                <a href="index.php?page=salary">
                    <i class="icon salary-icon"></i>
                    <span>Lương thưởng</span>
                </a>
            </li>
            <li class="<?php echo ($page == 'assessment') ? 'active' : ''; ?>">
                <a href="index.php?page=assessment">
                    <i class="icon assessment-icon"></i>
                    <span>Đánh giá</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link">
            <i class="icon logout-icon"></i>
            <span>Đăng xuất</span>
        </a>
    </div>
</aside>
