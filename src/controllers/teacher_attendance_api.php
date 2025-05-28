<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

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
    $studentsCollection = $db->students;
    $attendanceCollection = $db->attendance;
    $teacherId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET' && isset($_GET['classId'])) {
        if (isset($_GET['action']) && $_GET['action'] === 'summary') {
            $classId = $_GET['classId'];
            $today = new DateTime('today', new DateTimeZone('Asia/Manila'));
            $startOfDay = new MongoDB\BSON\UTCDateTime($today->setTime(0, 0, 0)->getTimestamp() * 1000);
            $endOfDay = new MongoDB\BSON\UTCDateTime($today->setTime(23, 59, 59)->getTimestamp() * 1000);

            $class = $classesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($classId), 'teacher' => $teacherId]);
            if (!$class) {
                throw new Exception('Class not found or unauthorized', 404);
            }

            $attendanceRecords = $attendanceCollection->find([
                'classId' => $classId,
                'date' => ['$gte' => $startOfDay, '$lte' => $endOfDay]
            ])->toArray();

            $summary = ['present' => 0, 'absent' => 0, 'late' => 0];
            foreach ($attendanceRecords as $record) {
                $status = $record['status'] ?? 'absent';
                if (isset($summary[$status])) {
                    $summary[$status]++;
                }
            }

            echo json_encode($summary);
        } else {
            $classId = $_GET['classId'];
            $class = $classesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($classId), 'teacher' => $teacherId]);
            if (!$class) {
                throw new Exception('Class not found or unauthorized', 404);
            }

            $studentIds = isset($class['students']) ? (array)$class['students'] : [];
            $studentIds = array_filter($studentIds, function($id) {
                try {
                    return $id instanceof MongoDB\BSON\ObjectId ? $id : new MongoDB\BSON\ObjectId($id);
                } catch (Exception $e) {
                    error_log("Invalid student ID in class $classId: " . $e->getMessage());
                    return false;
                }
            });

            $students = [];
            if (!empty($studentIds)) {
                $studentDocs = $studentsCollection->find(['_id' => ['$in' => array_values($studentIds)]])->toArray();
                foreach ($studentDocs as $student) {
                    $students[] = [
                        '_id' => (string)$student['_id'],
                        'name' => $student['name'] ?? 'Unknown Student'
                    ];
                }
            }

            echo json_encode($students);
        }
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['classId']) || !isset($input['date']) || !isset($input['attendanceRecords'])) {
            throw new Exception('Invalid request data', 400);
        }

        $classId = $input['classId'];
        $date = new DateTime($input['date'], new DateTimeZone('Asia/Manila'));
        $attendanceRecords = $input['attendanceRecords'];

        $class = $classesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($classId), 'teacher' => $teacherId]);
        if (!$class) {
            throw new Exception('Class not found or unauthorized', 404);
        }

        $studentIds = isset($class['students']) ? (array)$class['students'] : [];
        $studentIds = array_map(function($id) {
            return $id instanceof MongoDB\BSON\ObjectId ? (string)$id : $id;
        }, $studentIds);

        $validRecords = [];
        foreach ($attendanceRecords as $record) {
            if (!isset($record['studentId']) || !isset($record['status']) || !in_array($record['status'], ['present', 'absent', 'late'])) {
                continue;
            }
            if (!in_array($record['studentId'], $studentIds)) {
                continue;
            }
            $validRecords[] = [
                'studentId' => new MongoDB\BSON\ObjectId($record['studentId']),
                'status' => $record['status']
            ];
        }

        if (empty($validRecords)) {
            throw new Exception('No valid attendance records provided', 400);
        }

        foreach ($validRecords as $record) {
            $attendanceCollection->updateOne(
                [
                    'classId' => $classId,
                    'studentId' => $record['studentId'],
                    'date' => new MongoDB\BSON\UTCDateTime($date->setTime(0, 0, 0)->getTimestamp() * 1000)
                ],
                [
                    '$set' => [
                        'classId' => $classId,
                        'studentId' => $record['studentId'],
                        'status' => $record['status'],
                        'date' => new MongoDB\BSON\UTCDateTime($date->setTime(0, 0, 0)->getTimestamp() * 1000),
                        'teacherId' => $teacherId
                    ]
                ],
                ['upsert' => true]
            );
        }

        echo json_encode(['message' => 'Attendance saved successfully']);
    } else {
        throw new Exception('Invalid request method', 405);
    }

} catch (MongoDB\Driver\Exception\Exception $e) {
    error_log("MongoDB error in teacher_attendance_api.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'code' => 500]);
} catch (Exception $e) {
    error_log("Error in teacher_attendance_api.php: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage(), 'code' => $e->getCode() ?: 500]);
}