<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /EduRegistrar/public/register');
    exit;
}

$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];
$role = $_POST['role'];

// Validate inputs
if (!$name || !$email || !$password || !in_array($role, ['admin', 'teacher'])) {
    error_log("Registration failed: Invalid input");
    header('Location: /EduRegistrar/public/register?error=Invalid input');
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Registration failed: Invalid email format");
    header('Location: /EduRegistrar/public/register?error=Invalid email format');
    exit;
}

// Validate password length
if (strlen($password) < 8) {
    error_log("Registration failed: Password too short");
    header('Location: /EduRegistrar/public/register?error=Password must be at least 8 characters');
    exit;
}

try {
    $db = getMongoDB();

    // Check for existing email
    $existingUser = $db->users->findOne(['email' => $email]);
    if ($existingUser) {
        error_log("Registration failed: Email already exists");
        header('Location: /EduRegistrar/public/register?error=Email already exists');
        exit;
    }

    // Insert new user
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $result = $db->users->insertOne([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => $role,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    if ($result->getInsertedCount() === 1) {
        // Redirect to login page with success message
        header('Location: /EduRegistrar/public/?success=Account created successfully. Please sign in.');
        exit;
    } else {
        error_log("Registration failed: Database insert failed");
        header('Location: /EduRegistrar/public/register?error=Registration failed');
        exit;
    }
} catch (Exception $e) {
    error_log("Registration failed: MongoDB error - " . $e->getMessage());
    header('Location: /EduRegistrar/public/register?error=Database error');
    exit;
}
?>