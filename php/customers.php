<?php
require_once '../includes/auth.php'; // Admin only
require_once '../includes/db_connect.php';

$message = '';
$message_type = '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle actions: delete user (deactivation can be an update to a 'status' field if added)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id_to_action = $_POST['user_id'];

    // Prevent admin from deleting their own account through this panel
    if ($user_id_to_action == $_SESSION['user_id']) {
        $message = "You cannot delete your own account from the customer panel.";
        $message_type = "error";
    } else {
        if ($_POST['action'] === 'delete') {
            // Deleting a user might have implications (e.g., associated requests).
            // For now, a simple delete. Consider soft deletes or reassigning requests in a real app.
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $user_id_to_action);
                if ($stmt->execute()) {
                    $message = "User ID $user_id_to_action deleted successfully.";
                    $message_type = "success";
                } else {
                    $message = "Error deleting user: " . $stmt->error;
                    $message_type = "error";
                }
                $stmt->close();
            } else {
                $message = "Error preparing statement: " . $conn->error;
                $message_type = "error";
            }
        }
        // Add 'deactivate' logic here if a 'status' column is added to users table
        // elseif ($_POST['action'] === 'deactivate') { ... }
    }
}

// Fetch users
$sql = "SELECT id, name, email, phone, join_date FROM users";
$params = [];
$types = "";

if (!empty($search_term)) {
    $sql .= " WHERE name LIKE ? OR email LIKE ?";
    $like_search_term = "%" . $search_term . "%";
    $params[] = $like_search_term;
    $params[] = $like_search_term;
    $types .= "ss";
}
$sql .= " ORDER BY join_date DESC";

$stmt_fetch = $conn->prepare($sql);

if (!empty($search_term) && $stmt_fetch) {
    $stmt_fetch->bind_param($types, ...$params);
}

$customers = [];
if ($stmt_fetch) {
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
    }
    $stmt_fetch->close();
} else {
    // Handle statement preparation error if needed
     $message = "Error preparing user fetch statement: " . $conn->error;
     $message_type = "error";
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="customers.php" class="active">Customers</a></li>
                    <li><a href="requests.php">Requests</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Manage Customers</h1>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <section class="search-filter-section">
                <form action="customers.php" method="GET" class="filter-form">
                    <label for="search">Search by Name or Email:</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Enter name or email">
                    <button type="submit">Search</button>
                     <?php if (!empty($search_term)): ?>
                        <a href="customers.php" class="button" style="margin-left: 10px; background-color: #6c757d;">Clear Search</a>
                    <?php endif; ?>
                </form>
            </section>

            <!-- Add New Customer button/link can be added here if needed -->
            <!-- <a href="add_customer.php" class="button">Add New Customer</a> -->


            <section class="customers-list">
                <?php if (empty($customers) && !empty($search_term)): ?>
                    <p>No customers found matching your search criteria "<?php echo htmlspecialchars($search_term); ?>".</p>
                <?php elseif (empty($customers)): ?>
                     <p>No customers registered yet.</p>
                <?php else: ?>
                    <table class="content-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['id']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($customer['join_date']))); ?></td>
                                    <td class="action-links">
                                        <?php if ($customer['id'] != $_SESSION['user_id']): // Prevent self-action ?>
                                            <form action="customers.php<?php echo !empty($search_term) ? '?search='.urlencode($search_term) : ''; ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                <input type="hidden" name="user_id" value="<?php echo $customer['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn-delete">Delete</button>
                                            </form>
                                            <!-- Add deactivate button/logic here if implementing deactivation -->
                                            <!-- <form action="customers.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $customer['id']; ?>">
                                                <input type="hidden" name="action" value="deactivate">
                                                <button type="submit" class="btn-deactivate">Deactivate</button>
                                            </form> -->
                                        <?php else: ?>
                                            N/A (Current User)
                                        <?php endif; ?>
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
