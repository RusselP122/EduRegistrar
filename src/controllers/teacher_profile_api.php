<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name = filter_var($input['name'] ?? '', FILTER_SANITIZE_STRING);
$email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);

if (!$name || !$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid name or email']);
    exit;
}

try {
    $db = getMongoDB();
    $user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    $db->users->updateOne(
        ['_id' => $user_id],
        ['$set' => ['name' => $name, 'email' => $email]]
    );
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Profile updated successfully']);
} catch (Exception $e) {
    error_log("Error updating profile: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>