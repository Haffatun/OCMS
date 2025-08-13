<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower($_SESSION['role']);

// Redirect based on role
switch ($role) {
    case 'admin':
        header("Location: admin/dashboard.php");
        exit;
    case 'instructor':
        header("Location: instructor/dashboard.php");
        exit;
    case 'student':
        header("Location: student/dashboard.php");
        exit;
    default:
        // Role not recognized - log out
        session_destroy();
        header("Location: login.php?error=invalid_role");
        exit;
}
