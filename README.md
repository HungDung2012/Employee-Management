# Employee Management System

![Employee Management System](images/avatar.png)

## 📋 Overview

A comprehensive employee management system designed for corporate HR departments. This web-based application helps organizations manage their workforce efficiently with modules for recruitment, employee records, contracts, departments, attendance tracking, salary management, and performance assessments.

## ✨ Features

- **Dashboard**: Visualize employee statistics and department information
- **Recruitment**: Manage job postings and candidate applications
- **Employee Management**: Store and update comprehensive employee information
- **Contract Management**: Track employment contracts and terms
- **Department Organization**: Manage department structure and leadership
- **Attendance Tracking**: Record and monitor employee attendance
- **Salary Management**: Calculate and manage salary and bonus payments
- **Performance Assessment**: Conduct and record employee evaluations

## 🛠️ Technologies

- **Backend**: PHP, MySQL
- **Frontend**: HTML, CSS, JavaScript
- **Visualization**: Chart.js
- **Icons**: Font Awesome

## 💻 Installation

1. Clone the repository
   ```bash
   git clone https://github.com/yourusername/employee-management.git
   ```

2. Create a MySQL database and import the schema
   ```bash
   mysql -u username -p database_name < database.sql
   ```

3. Configure the database connection in `includes/db_connect.php`
   ```php
   $host = 'localhost';
   $dbname = 'CompanyManagement';
   $username = 'your_username';
   $password = 'your_password';
   ```

4. Deploy the application to your web server

## 🚀 Usage

1. Access the application through your web browser
2. Log in using your credentials (default admin: username `admin`, password `admin`)
3. Navigate through the sidebar to access different modules

## 🔒 Security

- Password hashing for user authentication
- Session-based authentication
- Input validation and sanitization

## 🌐 Languages

- English
- Vietnamese (Tiếng Việt)

## 📊 Database Structure

The system uses a relational database with tables for:
- Users
- Employees
- Departments
- Contracts
- Attendance records
- Salary information
- Performance assessments

## 👥 Contributors

- DCH Management Team

## 📝 License

This project is proprietary software. Unauthorized copying, modification, distribution, or use is strictly prohibited.
