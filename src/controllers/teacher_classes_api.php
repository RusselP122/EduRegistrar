<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Disable error display
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:\xampp\php\logs\php_error_log');

try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
        throw new Exception('Unauthorized access', 403);
    }

    $db = getMongoDB();
    if (!$db) {
        throw new Exception('Database connection failed', 500);
    }

    $classesCollection = $db->classes;
    $teacherId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    $classes = $classesCollection->find(['teacher' => $teacherId])->toArray() ?? [];

    $result = [];
    foreach ($classes as $class) {
        $result[] = [
            '_id' => (string)$class['_id'],
            'name' => $class['class_name'] ?? 'Unnamed Class',
            'studentCount' => isset($class['students']) ? count((array)$class['students']) : 0
        ];
    }

    error_log("API teacher classes response: " . print_r($result, true));
    echo json_encode($result);

} catch (MongoDB\Driver\Exception\Exception $e) {
    error_log("MongoDB error in teacher_classes_api.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'code' => 500]);
} catch (Exception $e) {
    error_log("Error in teacher_classes_api.php: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage(), 'code' => $e->getCode() ?: 500]);
}