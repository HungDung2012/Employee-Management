<?php
// This script creates the Users table and adds an admin user
// Run this script once to set up the authentication system

// Database connection
include 'includes/db_connect.php';

try {
    // Create Users table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS Users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role VARCHAR(50) NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Users table created successfully.<br>";
    
    // Check if admin user exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM Users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = (int)$stmt->fetchColumn();
    
    if ($adminExists === 0) {
        // Create default admin user
        // In a production environment, use password_hash() for secure password storage
        $username = 'admin';
        $password = 'admin123'; // Plain text for demonstration
        // $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Use this in production
        $fullName = 'Administrator';
        $role = 'Quản trị viên';
        
        $stmt = $conn->prepare("INSERT INTO Users (username, password, full_name, role) VALUES (:username, :password, :full_name, :role)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password); // Use $hashedPassword in production
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        
        echo "Default admin user created successfully.<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Admin user already exists.<br>";
    }
    
    echo "<p>Setup completed. <a href='login.php'>Go to login page</a></p>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
