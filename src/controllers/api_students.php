<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    // Validate admin access
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized', 403);
    }

    $db = getMongoDB();
    $collection = $db->students;
    
    $students = $collection->find(['role' => 'student'])->toArray();
    
    $result = array_map(function($student) {
        return [
            '_id' => (string)$student['_id'],
            'name' => $student['name'] ?? 'Unknown',
            'email' => $student['email'] ?? 'Unknown',
            'year' => $student['year'] ?? '',
            'section' => $student['section'] ?? ''
        ];
    }, $students);

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
    exit;
}