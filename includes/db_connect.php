<?php
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "business_app_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === FALSE) {
    // Log error or handle more gracefully in production
    die("Error creating database: " . $conn->error);
}

// Select the database
if (!$conn->select_db($dbname)) {
    die("Error selecting database: " . $conn->error);
}

// SQL to create users table
$sql_create_users_table = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_users_table) === FALSE) {
    die("Error creating table 'users': " . $conn->error);
}

// SQL to create requests table
$sql_create_requests_table = "
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    request_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending', -- e.g., Pending, Resolved
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_date TIMESTAMP NULL
)";

if ($conn->query($sql_create_requests_table) === FALSE) {
    die("Error creating table 'requests': " . $conn->error);
}

// Connection successful and tables ensured.
// In a production environment, logging would be preferred over die().
?>
