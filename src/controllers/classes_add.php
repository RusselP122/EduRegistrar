<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized access', 403);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method', 405);
    }

    $db = getMongoDB();
    if (!$db) {
        throw new Exception('Database connection failed', 500);
    }

    $classesCollection = $db->classes;
    $studentsCollection = $db->students;
    $usersCollection = $db->users;

    // Get form data
    $className = $_POST['class_name'] ?? null;
    $teacherId = $_POST['teacher'] ?? null;
    $studentIds = $_POST['students'] ?? [];

    if (!$className) {
        throw new Exception('Class name is required', 400);
    }

    // Validate teacher ID
    $teacher = null;
    if (!empty($teacherId)) {
        try {
            $teacherObjectId = new MongoDB\BSON\ObjectId($teacherId);
            $teacherDoc = $usersCollection->findOne(['_id' => $teacherObjectId, 'role' => 'teacher']);
            if (!$teacherDoc) {
                throw new Exception('Invalid or non-existent teacher ID', 400);
            }
            $teacher = $teacherObjectId;
        } catch (MongoDB\Driver\Exception\InvalidArgumentException $e) {
            throw new Exception('Invalid teacher ID format', 400);
        }
    }

    // Validate student IDs
    $validStudentIds = [];
    $studentIds = is_array($studentIds) ? $studentIds : [$studentIds];
    error_log("Class add attempt: class_name=$className, teacher=$teacherId, students=" . print_r($studentIds, true));

    foreach ($studentIds as $studentId) {
        try {
            $studentObjectId = new MongoDB\BSON\ObjectId($studentId);
            $studentDoc = $studentsCollection->findOne(['_id' => $studentObjectId]);
            if ($studentDoc) {
                $validStudentIds[] = $studentObjectId;
                error_log("Valid student ID: $studentId, name: " . ($studentDoc['name'] ?? 'Unknown'));
            } else {
                error_log("Invalid student ID (not found): $studentId");
            }
        } catch (MongoDB\Driver\Exception\InvalidArgumentException $e) {
            error_log("Invalid student ID format: $studentId");
        }
    }

    // Prepare class document
    $classDoc = [
        'class_name' => $className,
        'students' => $validStudentIds,
        'teacher' => $teacher
    ];

    // Insert class
    $insertResult = $classesCollection->insertOne($classDoc);
    error_log("Insert result: insertedCount=" . $insertResult->getInsertedCount() . ", insertedId=" . $insertResult->getInsertedId());

    if ($insertResult->getInsertedCount() !== 1) {
        throw new Exception('Failed to add class', 500);
    }

    echo json_encode(['success' => true]);

} catch (MongoDB\Driver\Exception\Exception $e) {
    error_log("MongoDB error in classes_add.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'code' => 500]);
} catch (Exception $e) {
    error_log("Error in classes_add.php: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage(), 'code' => $e->getCode() ?: 500]);
}