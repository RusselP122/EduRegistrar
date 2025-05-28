<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $db = getMongoDB();
    $teachers = $db->users->find(['role' => 'teacher'])->toArray();
    $result = array_map(function($teacher) {
        return [
            '_id' => (string)$teacher['_id'],
            'name' => $teacher['name'],
            'email' => $teacher['email']
        ];
    }, $teachers);
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error fetching teachers: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>