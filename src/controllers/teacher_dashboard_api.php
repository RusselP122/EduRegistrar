<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Disable error display to prevent HTML output in JSON
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
    $today = new DateTime('today', new DateTimeZone('Asia/Manila')); // Adjust timezone as needed

    // Total classes
    $totalClasses = $classesCollection->countDocuments(['teacher' => $teacherId]);

    // Classes today (since no schedule field, assume all classes are relevant)
    $todayClasses = $classesCollection->find(['teacher' => $teacherId])->toArray() ?? [];

    $todayClassesFormatted = [];
    foreach ($todayClasses as $class) {
        $todayClassesFormatted[] = [
            '_id' => (string)$class['_id'],
            'name' => $class['class_name'] ?? 'Unnamed Class',
            'studentCount' => isset($class['students']) ? count((array)$class['students']) : 0
        ];
    }

    $result = [
        'totalClasses' => $totalClasses,
        'classesToday' => count($todayClassesFormatted),
        'nextClassTime' => '--:--', // No schedule field, so default to '--:--'
        'todayClasses' => $todayClassesFormatted
    ];

    error_log("API teacher dashboard response: " . print_r($result, true));
    echo json_encode($result);

} catch (MongoDB\Driver\Exception\Exception $e) {
    error_log("MongoDB error in teacher_dashboard_api.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'code' => 500]);
} catch (Exception $e) {
    error_log("Error in teacher_dashboard_api.php: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage(), 'code' => $e->getCode() ?: 500]);
}