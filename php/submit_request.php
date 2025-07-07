<?php
// This page can be public or require login depending on requirements.
// For now, let's assume it's a public form for anyone to submit a request.
// If it's for logged-in users only, uncomment the next line:
// require_once '../includes/auth.php';
session_start(); // Start session to display messages
require_once '../includes/db_connect.php';

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $request_type = $_POST['request_type'];
    $description = $_POST['description'];

    // Basic validation
    if (empty($customer_name) || empty($customer_email) || empty($request_type) || empty($description)) {
        $message = "All fields are required.";
        $message_type = "error";
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO requests (customer_name, customer_email, request_type, description, status) VALUES (?, ?, ?, ?, 'Pending')");
        if ($stmt === false) {
            // Log error: $conn->error
            $message = "Error preparing statement. Please try again later.";
            $message_type = "error";
        } else {
            $stmt->bind_param("ssss", $customer_name, $customer_email, $request_type, $description);
            if ($stmt->execute()) {
                $message = "Your request has been submitted successfully! We will get back to you soon.";
                $message_type = "success";
                // Clear form fields after successful submission if desired, by redirecting or clearing POST.
                // For simplicity, we're just showing a message.
                // header("Location: submit_request.php?status=success"); exit();
            } else {
                // Log error: $stmt->error
                $message = "Error submitting your request. Please try again.<!-- Error: " . $stmt->error . " -->";
                $message_type = "error";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Service Request - Business Management App</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Submit a Service Request</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="submit_request.php" method="POST">
            <label for="customer_name">Your Name:</label>
            <input type="text" id="customer_name" name="customer_name" required value="<?php echo isset($_POST['customer_name']) && $message_type === 'error' ? htmlspecialchars($_POST['customer_name']) : ''; ?>">

            <label for="customer_email">Your Email:</label>
            <input type="email" id="customer_email" name="customer_email" required value="<?php echo isset($_POST['customer_email']) && $message_type === 'error' ? htmlspecialchars($_POST['customer_email']) : ''; ?>">

            <label for="request_type">Request Type:</label>
            <select id="request_type" name="request_type" required>
                <option value="">-- Select a Request Type --</option>
                <option value="General Inquiry" <?php echo (isset($_POST['request_type']) && $_POST['request_type'] === 'General Inquiry' && $message_type === 'error') ? 'selected' : ''; ?>>General Inquiry</option>
                <option value="Technical Support" <?php echo (isset($_POST['request_type']) && $_POST['request_type'] === 'Technical Support' && $message_type === 'error') ? 'selected' : ''; ?>>Technical Support</option>
                <option value="Billing Question" <?php echo (isset($_POST['request_type']) && $_POST['request_type'] === 'Billing Question' && $message_type === 'error') ? 'selected' : ''; ?>>Billing Question</option>
                <option value="Service Feedback" <?php echo (isset($_POST['request_type']) && $_POST['request_type'] === 'Service Feedback' && $message_type === 'error') ? 'selected' : ''; ?>>Service Feedback</option>
                <option value="Other" <?php echo (isset($_POST['request_type']) && $_POST['request_type'] === 'Other' && $message_type === 'error') ? 'selected' : ''; ?>>Other</option>
            </select>

            <label for="description">Description of Request:</label>
            <textarea id="description" name="description" rows="6" required><?php echo isset($_POST['description']) && $message_type === 'error' ? htmlspecialchars($_POST['description']) : ''; ?></textarea>

            <button type="submit">Submit Request</button>
        </form>
        <p><a href="index.php">Back to Home</a></p> <!-- Assuming index.php is the main landing page -->
    </div>
</body>
</html>
