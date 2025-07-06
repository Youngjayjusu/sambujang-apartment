<?php
require_once '../includes/auth.php'; // Admin only
require_once '../includes/db_connect.php';

$message = '';
$message_type = '';

// Handle actions: mark as resolved, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['request_id'])) {
        $request_id = $_POST['request_id'];

        if ($_POST['action'] === 'resolve') {
            $stmt = $conn->prepare("UPDATE requests SET status = 'Resolved', resolved_date = CURRENT_TIMESTAMP WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $request_id);
                if ($stmt->execute()) {
                    $message = "Request ID $request_id marked as Resolved.";
                    $message_type = "success";
                } else {
                    $message = "Error updating request: " . $stmt->error;
                    $message_type = "error";
                }
                $stmt->close();
            } else {
                $message = "Error preparing statement: " . $conn->error;
                $message_type = "error";
            }
        } elseif ($_POST['action'] === 'delete') {
            // Optional: Add confirmation before deleting
            $stmt = $conn->prepare("DELETE FROM requests WHERE id = ?");
             if ($stmt) {
                $stmt->bind_param("i", $request_id);
                if ($stmt->execute()) {
                    $message = "Request ID $request_id deleted.";
                    $message_type = "success";
                } else {
                    $message = "Error deleting request: " . $stmt->error;
                    $message_type = "error";
                }
                $stmt->close();
            } else {
                $message = "Error preparing statement: " . $conn->error;
                $message_type = "error";
            }
        }
    }
}


// Fetch requests
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$sql = "SELECT id, customer_name, customer_email, request_type, description, status, submission_date, resolved_date FROM requests";

if ($filter_status !== 'all') {
    $sql .= " WHERE status = ?";
}
$sql .= " ORDER BY submission_date DESC";

$stmt_fetch = $conn->prepare($sql);

if ($filter_status !== 'all') {
    $stmt_fetch->bind_param("s", $filter_status);
}

$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
$requests = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
$stmt_fetch->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests - Admin Dashboard</title>
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
                    <li><a href="requests.php" class="active">Requests</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Manage Customer Requests</h1>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <section class="filter-form-section">
                <form action="requests.php" method="GET" class="filter-form">
                    <label for="status">Filter by status:</label>
                    <select name="status" id="status">
                        <option value="all" <?php if ($filter_status === 'all') echo 'selected'; ?>>All</option>
                        <option value="Pending" <?php if ($filter_status === 'Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Resolved" <?php if ($filter_status === 'Resolved') echo 'selected'; ?>>Resolved</option>
                    </select>
                    <button type="submit">Filter</button>
                </form>
            </section>

            <section class="requests-list">
                <?php if (empty($requests)): ?>
                    <p>No requests found<?php echo ($filter_status !== 'all') ? ' for the selected status' : ''; ?>.</p>
                <?php else: ?>
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Resolved On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['id']); ?></td>
                                    <td><?php echo htmlspecialchars($request['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['customer_email']); ?></td>
                                    <td><?php echo htmlspecialchars($request['request_type']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars(substr($request['description'], 0, 100))) . (strlen($request['description']) > 100 ? '...' : ''); ?></td>
                                    <td><span class="status-<?php echo strtolower(htmlspecialchars($request['status'])); ?>"><?php echo htmlspecialchars($request['status']); ?></span></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($request['submission_date']))); ?></td>
                                    <td><?php echo $request['resolved_date'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($request['resolved_date']))) : 'N/A'; ?></td>
                                    <td class="action-links">
                                        <?php if ($request['status'] === 'Pending'): ?>
                                            <form action="requests.php<?php echo $filter_status !== 'all' ? '?status='.$filter_status : ''; ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <input type="hidden" name="action" value="resolve">
                                                <button type="submit" class="btn-resolve">Resolve</button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="requests.php<?php echo $filter_status !== 'all' ? '?status='.$filter_status : ''; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this request?');">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                         <!-- Add a view details link/modal later if needed -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
