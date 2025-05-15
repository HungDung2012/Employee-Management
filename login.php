<?php
// Start session
session_start();

// Database connection
include 'includes/db_connect.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard
    header("Location: index.php");
    exit;
}

// Initialize variables
$username = "";
$password = "";
$error = "";

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập tên đăng nhập và mật khẩu.";
    } else {
        try {
            // Check if Users table exists
            $stmt = $conn->prepare("SHOW TABLES LIKE 'users'");
            $stmt->execute();
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                // Create Users table if it doesn't exist
                $sql = "CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    full_name VARCHAR(100) NOT NULL,
                    role VARCHAR(50) NOT NULL,
                    email VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $conn->exec($sql);
                
                // Create default admin user
                $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (:username, :password, :full_name, :role)");
                $adminUsername = 'admin';
                $adminPassword = 'admin123';
                $adminFullName = 'Administrator';
                $adminRole = 'Quản trị viên';
                
                $stmt->bindParam(':username', $adminUsername);
                $stmt->bindParam(':password', $adminPassword);
                $stmt->bindParam(':full_name', $adminFullName);
                $stmt->bindParam(':role', $adminRole);
                $stmt->execute();
                
                $error = "Đã tạo tài khoản admin mặc định. Vui lòng đăng nhập với tên đăng nhập 'admin' và mật khẩu 'admin123'.";
            }
            
            // Check user credentials - use lowercase 'users' instead of 'Users'
            $stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password (using password_verify if passwords are hashed)
                if (password_verify($password, $user['password']) || $password === $user['password']) { // Second condition for plain text passwords
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Redirect to dashboard
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Mật khẩu không chính xác.";
                }
            } else {
                $error = "Tên đăng nhập không tồn tại.";
            }
        } catch (PDOException $e) {
            $error = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Quản lý Nhân viên</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        
        .login-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 400px;
            max-width: 90%;
            padding: 30px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .login-form .form-group {
            margin-bottom: 20px;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .login-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .login-form input:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .login-form button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-form button:hover {
            background-color: #2980b9;
        }
        
        .error-message {
            background-color: #fadbd8;
            color: #c0392b;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .company-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .company-logo img {
            max-width: 100px;
            height: auto;
        }
        
        .back-to-home {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-home a {
            color: #3498db;
            text-decoration: none;
        }
        
        .back-to-home a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="company-logo">
            <img src="images/company-logo.png" alt="Company Logo" onerror="this.src='images/avatar.png'">
        </div>
        
        <div class="login-header">
            <h1>Hệ thống Quản lý Nhân viên</h1>
            <p>Vui lòng đăng nhập để tiếp tục</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Đăng nhập</button>
        </form>
        
        <div class="back-to-home">
            <a href="homepage.php">← Quay lại trang chủ</a>
        </div>
    </div>
</body>
</html>
