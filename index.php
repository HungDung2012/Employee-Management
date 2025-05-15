<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: homepage.php");
    exit;
}

// Database connection
include 'includes/db_connect.php';

// Default page is dashboard
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Include header
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <?php
            // Load the appropriate page based on the 'page' parameter
            switch($page) {
                case 'homepage':
                    include 'homepage.php';
                    break;
                case 'dashboard':
                    include 'pages/dashboard.php';
                    break;
                case 'recruitment':
                    include 'pages/recruitment.php';
                    break;
                case 'employees':
                    include 'pages/employees.php';
                    break;
                case 'contracts':
                    include 'pages/contracts.php';
                    break;
                case 'departments':
                    include 'pages/departments.php';
                    break;
                case 'salary':
                    include 'pages/salary.php';
                    break;
                case 'assessment':
                    include 'pages/assessment.php';
                    break;
                case 'attendance':
                    include 'pages/attendance.php';
                    break;
                default:
                    include 'pages/dashboard.php';
            }
            ?>
        </main>
    </div>
</body>
</html>
