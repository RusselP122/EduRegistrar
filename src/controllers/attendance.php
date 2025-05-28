<?php
require_once __DIR__ . '/../config/db.php';

function markAttendance($classId, $statuses) {
    $db = getMongoDB();
    foreach ($statuses as $studentId => $status) {
        $db->attendance->insertOne([
            'class_id' => new MongoDB\BSON\ObjectId($classId),
            'student_id' => new MongoDB\BSON\ObjectId($studentId),
            'date' => new MongoDB\BSON\UTCDateTime(),
            'status' => $status,
            'marked_by' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    if ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'admin') {
        markAttendance($_POST['class_id'], $_POST['status']);
        header('Location: /dashboard');
    } else {
        echo "Unauthorized";
    }
}
?>