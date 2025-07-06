<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        die("Email and password are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $user_name, $hashed_password_from_db);
        $stmt->fetch();

        if (password_verify($password, $hashed_password_from_db)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $user_name;
            // Regenerate session ID for security
            session_regenerate_id(true);
            header("Location: dashboard.php");
            exit();
        } else {
            // Log failed login attempt
            echo "Invalid email or password. <a href='login.php'>Try again</a>";
        }
    } else {
        // Log failed login attempt (user not found)
        echo "Invalid email or password. <a href='login.php'>Try again</a>";
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: login.php");
    exit();
}
?>
