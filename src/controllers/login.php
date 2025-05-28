<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /EduRegistrar/public/');
    exit;
}

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];
$role = $_POST['role'];
$remember = isset($_POST['remember']);

// Validate inputs
if (!$email || !$password || !in_array($role, ['admin', 'teacher'])) {
    error_log("Login failed: Missing or invalid input");
    header('Location: /EduRegistrar/public/?error=Please fill in all fields and select a valid role');
    exit;
}

try {
    $db = getMongoDB();
    $user = $db->users->findOne(['email' => $email]);

    if (!$user || !password_verify($password, $user['password']) || $user['role'] !== $role) {
        error_log("Login failed: Invalid email, password, or role");
        header('Location: /EduRegistrar/public/?error=Invalid email, password, or role');
        exit;
    }

    // Set session variables
    $_SESSION['user_id'] = (string)$user['_id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // Handle "Remember Me"
    if ($remember) {
        setcookie('user_id', $_SESSION['user_id'], time() + (30 * 24 * 60 * 60), '/EduRegistrar/public/');
    }

    // Redirect based on role
    $redirect = $user['role'] === 'admin' ? '/dashboard' : '/teacher/dashboard';
    header("Location: /EduRegistrar/public$redirect");
    exit;
} catch (Exception $e) {
    error_log("Login failed: MongoDB error - " . $e->getMessage());
    header('Location: /EduRegistrar/public/?error=Database error');
    exit;
}
?>