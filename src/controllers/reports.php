<?php
require_once __DIR__ . '/../config/db.php';

function generateReport($classId, $startDate, $endDate) {
    $db = getMongoDB();
    $records = $db->attendance->find([
        'class_id' => new MongoDB\BSON\ObjectId($classId),
        'date' => [
            '$gte' => new MongoDB\BSON\UTCDateTime(strtotime($startDate) * 1000),
            '$lte' => new MongoDB\BSON\UTCDateTime(strtotime($endDate) * 1000)
        ]
    ])->toArray();

    $report = [];
    foreach ($records as $record) {
        $studentId = (string)$record['student_id'];
        if (!isset($report[$studentId])) {
            $report[$studentId] = ['present' => 0, 'absent' => 0, 'late' => 0];
        }
        $report[$studentId][$record['status']]++;
    }
    return $report;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $db = getMongoDB();
    $filter = [
        'class_id' => new MongoDB\BSON\ObjectId($_GET['class_id']),
        'date' => [
            '$gte' => new MongoDB\BSON\UTCDateTime(strtotime($_GET['start_date']) * 1000),
            '$lte' => new MongoDB\BSON\UTCDateTime(strtotime($_GET['end_date']) * 1000)
        ]
    ];
    $attendances = $db->attendance->find($filter)->toArray();
    header('Content-Type: application/json');
    echo json_encode(array_map(function($record) {
        return [
            'teacher_id' => isset($record['teacher_id']) ? (string)$record['teacher_id'] : null,
            'date' => isset($record['date']) ? $record['date'] : null,
            'status' => isset($record['status']) ? $record['status'] : null
        ];
    }, $attendances));
}

