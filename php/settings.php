<?php
require_once '../includes/auth.php'; // Admin only
require_once '../includes/db_connect.php';

$message = '';
$message_type = '';
$user_id = $_SESSION['user_id'];

// Handle password update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All password fields are required.";
        $message_type = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "New password and confirm password do not match.";
        $message_type = "error";
    } elseif (strlen($new_password) < 6) { // Basic password strength check
        $message = "New password must be at least 6 characters long.";
        $message_type = "error";
    } else {
        // Fetch current password hash from DB
        $stmt_fetch = $conn->prepare("SELECT password FROM users WHERE id = ?");
        if ($stmt_fetch) {
            $stmt_fetch->bind_param("i", $user_id);
            $stmt_fetch->execute();
            $stmt_fetch->store_result();
            if ($stmt_fetch->num_rows > 0) {
                $stmt_fetch->bind_result($hashed_password_from_db);
                $stmt_fetch->fetch();

                if (password_verify($current_password, $hashed_password_from_db)) {
                    // Current password matches, proceed to update
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($stmt_update) {
                        $stmt_update->bind_param("si", $new_hashed_password, $user_id);
                        if ($stmt_update->execute()) {
                            $message = "Password updated successfully.";
                            $message_type = "success";
                        } else {
                            $message = "Error updating password: " . $stmt_update->error;
                            $message_type = "error";
                        }
                        $stmt_update->close();
                    } else {
                        $message = "Error preparing update statement: " . $conn->error;
                        $message_type = "error";
                    }
                } else {
                    $message = "Incorrect current password.";
                    $message_type = "error";
                }
            } else {
                 $message = "User not found."; // Should not happen if session is valid
                 $message_type = "error";
            }
            $stmt_fetch->close();
        } else {
            $message = "Error preparing fetch statement: " . $conn->error;
            $message_type = "error";
        }
    }
}

// Fetch current user's basic info (e.g., name, email for display)
$user_info_stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$user_name = $user_email = $user_phone = '';
if($user_info_stmt) {
    $user_info_stmt->bind_param("i", $user_id);
    $user_info_stmt->execute();
    $user_info_result = $user_info_stmt->get_result();
    if($user_info_row = $user_info_result->fetch_assoc()){
        $user_name = $user_info_row['name'];
        $user_email = $user_info_row['email'];
        $user_phone = $user_info_row['phone'];
    }
    $user_info_stmt->close();
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="customers.php">Customers</a></li>
                    <li><a href="requests.php">Requests</a></li>
                    <li><a href="settings.php" class="active">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Settings</h1>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <section class="form-section profile-info">
                <h3>Your Profile Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user_name); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_phone); ?></p>
                <!-- In a future version, allow editing these details -->
            </section>

            <section class="form-section">
                <h3>Change Your Password</h3>
                <form action="settings.php" method="POST">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" required>

                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>

                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>

                    <button type="submit" name="update_password">Update Password</button>
                </form>
            </section>

            <!-- Placeholder for other app settings -->
            <!--
            <section class="form-section">
                <h3>Application Settings</h3>
                <p>Future settings like color theme selection will appear here.</p>
            </section>
            -->

        </main>
    </div>
</body>
</html>
