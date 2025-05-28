<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$type = $_GET['type'] ?? 'daily';
if (!in_array($type, ['daily', 'weekly'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid report type']);
    exit;
}

try {
    $db = getMongoDB();
    $teacher_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    $filter = [
        'course_id' => ['$in' => array_map(fn($c) => $c['_id'], $db->courses->find(['teacher_id' => $teacher_id])->toArray())]
    ];
    if ($type === 'daily') {
        $start = new MongoDB\BSON\UTCDateTime(strtotime('today') * 1000);
        $end = new MongoDB\BSON\UTCDateTime(strtotime('tomorrow') * 1000);
        $filter['date'] = ['$gte' => $start, '$lt' => $end];
    } else {
        $start = new MongoDB\BSON\UTCDateTime(strtotime('-7 days') * 1000);
        $end = new MongoDB\BSON\UTCDateTime(strtotime('tomorrow') * 1000);
        $filter['date'] = ['$gte' => $start, '$lt' => $end];
    }

    $records = $db->attendance->aggregate([
        ['$match' => $filter],
        ['$lookup' => [
            'from' => 'courses',
            'localField' => 'course_id',
            'foreignField' => '_id',
            'as' => 'course'
        ]],
        ['$unwind' => '$course'],
        ['$group' => [
            '_id' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$date']],
            'className' => ['$first' => '$course.name'],
            'present' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'present']], 1, 0]]],
            'total' => ['$sum' => 1]
        ]],
        ['$project' => [
            'className' => 1,
            'date' => '$_id',
            'attendance' => ['$multiply' => [['$divide' => ['$present', '$total']], 100]],
            '_id' => 0
        ]]
    ])->toArray();

    header('Content-Type: application/json');
    echo json_encode($records);
} catch (Exception $e) {
    error_log("Error fetching reports: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>