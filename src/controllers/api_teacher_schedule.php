<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
        throw new Exception('Unauthorized access', 403);
    }

    $db = getMongoDB();
    if (!$db) {
        throw new Exception('Database connection failed', 500);
    }

    $classesCollection = $db->classes;

    // Convert teacher ID to ObjectId
    $teacherId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

    // Find classes assigned to the teacher
    $classes = $classesCollection->find(['teacher' => $teacherId], [
        'projection' => [
            'class_name' => 1,
            'schedule' => 1,
            'room' => 1
        ]
    ])->toArray() ?? [];

    $result = [];
    foreach ($classes as $class) {
        // Parse schedule (assuming format like "Mon 10:00 AM")
        $scheduleParts = explode(' ', $class['schedule'] ?? '');
        $day = $scheduleParts[0] ?? 'N/A';
        $time = count($scheduleParts) > 1 ? implode(' ', array_slice($scheduleParts, 1)) : 'N/A';

        $result[] = [
            'className' => $class['class_name'] ?? 'Unnamed Class',
            'day' => $day,
            'time' => $time,
            'room' => $class['room'] ?? 'Not assigned'
        ];
    }

    error_log("API teacher schedule response: " . print_r($result, true));
    echo json_encode($result ?: []);

} catch (MongoDB\Driver\Exception\Exception $e) {
    error_log("MongoDB error in api_teacher_schedule.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'code' => 500]);
} catch (Exception $e) {
    error_log("Error in api_teacher_schedule.php: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage(), 'code' => $e->getCode() ?: 500]);
}