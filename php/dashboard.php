<?php
require_once '../includes/auth.php'; // Ensures user is logged in
require_once '../includes/db_connect.php'; // For database operations

// Fetch summary data (placeholders for now, will be dynamic later)
$total_customers = 0;
$total_requests = 0;
$pending_requests = 0;
$resolved_requests = 0;

// Query for total customers
$result_customers = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result_customers && $result_customers->num_rows > 0) {
    $total_customers = $result_customers->fetch_assoc()['total'];
}

// Query for total requests
$result_total_requests = $conn->query("SELECT COUNT(*) AS total FROM requests");
if ($result_total_requests && $result_total_requests->num_rows > 0) {
    $total_requests = $result_total_requests->fetch_assoc()['total'];
}

// Query for pending requests
$result_pending_requests = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE status = 'Pending'");
if ($result_pending_requests && $result_pending_requests->num_rows > 0) {
    $pending_requests = $result_pending_requests->fetch_assoc()['total'];
}

// Query for resolved requests
$result_resolved_requests = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE status = 'Resolved'");
if ($result_resolved_requests && $result_resolved_requests->num_rows > 0) {
    $resolved_requests = $result_resolved_requests->fetch_assoc()['total'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Business Management App</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Home</a></li>
                    <li><a href="customers.php">Customers</a></li>
                    <li><a href="requests.php">Requests</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            </header>

            <section class="summary-panel">
                <div class="summary-card customers">
                    <h3>Total Customers</h3>
                    <p><?php echo $total_customers; ?></p>
                </div>
                <div class="summary-card requests">
                    <h3>Total Requests</h3>
                    <p><?php echo $total_requests; ?></p>
                </div>
                <div class="summary-card pending">
                    <h3>Pending Requests</h3>
                    <p><?php echo $pending_requests; ?></p>
                </div>
                <div class="summary-card resolved">
                    <h3>Resolved Requests</h3>
                    <p><?php echo $resolved_requests; ?></p>
                </div>
            </section>

            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <a href="submit_request.php" class="button">Add New Request (for Customer)</a>
                <!-- <a href="customers.php?action=add" class="button">Add New Customer</a> -->
                <!-- The "Add New Customer" functionality is implicitly handled by the registration form -->
                <!-- Or, if admins need to create users directly, a separate add_user.php could be made -->
            </section>

            <!-- Further sections for recent activity, charts, etc. can be added here -->

        </main>
    </div>
</body>
</html>
