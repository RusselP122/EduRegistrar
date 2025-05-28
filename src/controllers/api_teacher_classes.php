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
    $studentsCollection = $db->students;

    // Convert teacher ID to ObjectId
    $teacherId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

    // Find classes assigned to the teacher
    $classes = $classesCollection->find(['teacher' => $teacherId])->toArray() ?? [];

    $result = [];
    foreach ($classes as $class) {
        // Count valid students
        $studentCount = 0;
        $studentIdsRaw = isset($class['students']) ? (array)$class['students'] : [];
        error_log("Class {$class['_id']} raw student IDs: " . print_r($studentIdsRaw, true));

        if (!empty($studentIdsRaw)) {
            $studentIds = array_filter($studentIdsRaw, function($id) {
                try {
                    return $id instanceof MongoDB\BSON\ObjectId ? $id : new MongoDB\BSON\ObjectId($id);
                } catch (Exception $e) {
                    error_log("Invalid student ID in class {$class['_id']}: " . $e->getMessage());
                    return false;
                }
            });

            error_log("Class {$class['_id']} filtered student IDs: " . print_r($studentIds, true));

            if (!empty($studentIds)) {
                $studentCount = $studentsCollection->countDocuments([
                    '_id' => ['$in' => array_values($studentIds)]
                ]);
                error_log("Class {$class['_id']} student count: $studentCount");
            }
        }

        $result[] = [
            '_id' => (string)$class['_id'],
            'name' => $class['class_name'] ?? 'Unnamed Class',
            'room' => $class['room'] ?? 'Not assigned',
            'studentCount' => $studentCount,
            'schedule' => $class['schedule'] ?? 'Not scheduled'
        ];
    }

    error_log("API teacher classes response: " . print_r($result, true));
    echo json_encode($result ?: []);

} catch (MongoDB\Driver\Exception\Exception $e) {
    error_log("MongoDB error in api_teacher_classes.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'code' => 500]);
} catch (Exception $e) {
    error_log("Error in api_teacher_classes.php: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage(), 'code' => $e->getCode() ?: 500]);
}