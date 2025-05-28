<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        throw new Exception('Unauthorized access', 403);
    }

    $db = getMongoDB();
    if (!$db) {
        throw new Exception('Database connection failed', 500);
    }

    $classesCollection = $db->classes;
    $studentsCollection = $db->students;
    $usersCollection = $db->users;

    $classes = $classesCollection->find()->toArray() ?? [];

    $result = [];
    foreach ($classes as $class) {
        $students = [];
        // Cast BSONArray to PHP array
        $studentIdsRaw = isset($class['students']) ? (array)$class['students'] : [];
        error_log("Class {$class['_id']} raw student IDs: " . print_r($studentIdsRaw, true));

        if (!empty($studentIdsRaw)) {
            $studentIds = array_filter($studentIdsRaw, function($id) {
                try {
                    $objectId = $id instanceof MongoDB\BSON\ObjectId ? $id : new MongoDB\BSON\ObjectId($id);
                    return $objectId;
                } catch (Exception $e) {
                    error_log("Invalid student ID in class {$class['_id']}: " . $e->getMessage());
                    return false;
                }
            });

            error_log("Class {$class['_id']} filtered student IDs: " . print_r($studentIds, true));

            if (!empty($studentIds)) {
                $students = $studentsCollection->find([
                    '_id' => ['$in' => array_values($studentIds)]
                ])->toArray();
                error_log("Class {$class['_id']} students found: " . print_r($students, true));
            }
        }

        $teacher = null;
        if (!empty($class['teacher'])) {
            try {
                $teacherId = $class['teacher'] instanceof MongoDB\BSON\ObjectId ? $class['teacher'] : new MongoDB\BSON\ObjectId($class['teacher']);
                $teacherDoc = $usersCollection->findOne(['_id' => $teacherId, 'role' => 'teacher']);
                if ($teacherDoc) {
                    $teacher = [
                        '_id' => (string)$teacherDoc['_id'],
                        'name' => $teacherDoc['name'] ?? 'Unknown Teacher'
                    ];
                }
            } catch (Exception $e) {
                error_log("Invalid teacher ID in class {$class['_id']}: " . $e->getMessage());
            }
        }

        $result[] = [
            '_id' => (string)($class['_id'] ?? ''),
            'class_name' => $class['class_name'] ?? 'Unnamed Class',
            'students' => array_map(function($s) {
                return [
                    '_id' => (string)($s['_id'] ?? ''),
                    'name' => $s['name'] ?? 'Unknown Student'
                ];
            }, $students),
            'teacher' => $teacher
        ];
    }

    error_log("API classes response: " . print_r($result, true));
    echo json_encode($result ?: []);

} catch (MongoDB\Driver\Exception\Exception $e) {
    error_log("MongoDB error in api_classes.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'code' => 500]);
} catch (Exception $e) {
    error_log("Error in api_classes.php: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage(), 'code' => $e->getCode() ?: 500]);
}