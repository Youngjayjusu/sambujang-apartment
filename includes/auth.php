<?php
session_start();

// Check if the user is logged in.
// If not, redirect them to the login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Adjust path as necessary if this include is used in different directory levels
    exit;
}

// Optional: You can add more role-based access control here if needed in the future.
// For example, check if $_SESSION['user_role'] == 'admin' for certain pages.

// The user is authenticated, so the script calling this include can continue.
?>
