<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // Validate input
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        die("Please fill all required fields.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Check if email already exists
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        die("Email already registered. <a href='login.php'>Login here</a>");
    }
    $stmt_check->close();

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $stmt_insert = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
    if ($stmt_insert === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt_insert->bind_param("ssss", $name, $email, $phone, $hashed_password);

    if ($stmt_insert->execute()) {
        $_SESSION['user_id'] = $stmt_insert->insert_id; // Or use email, but id is safer
        $_SESSION['user_name'] = $name;
        // Redirect to dashboard or a success page
        header("Location: dashboard.php");
        exit();
    } else {
        // Log error: $stmt_insert->error
        die("Error registering user. Please try again. Error: " . $stmt_insert->error);
    }
    $stmt_insert->close();
    $conn->close();
} else {
    // Not a POST request
    header("Location: register.php");
    exit();
}
?>
