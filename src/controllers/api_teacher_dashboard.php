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
    $attendanceCollection = $db->attendance;

    $teacherId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    $today = new DateTime('today', new DateTimeZone('Asia/Manila')); // Adjust timezone as needed
    $startOfWeek = (clone $today)->modify('Monday this week')->setTime(0, 0, 0);
    $endOfWeek = (clone $startOfWeek)->modify('+6 days')->setTime(23, 59, 59);

    // Total classes
    $totalClasses = $classesCollection->countDocuments(['teacher' => $teacherId]);

    // Classes today
    $todayClasses = $classesCollection->find([
        'teacher' => $teacherId,
        'schedule' => new MongoDB\BSON\Regex("^{$today->format('D')}", 'i')
    ])->toArray() ?? [];

    $todayClassesFormatted = [];
    $nextClassTime = null;
    foreach ($todayClasses as $class) {
        $studentCount = 0;
        $studentIdsRaw = isset($class['students']) ? (array)$class['students'] : [];
        if (!empty($studentIdsRaw)) {
            $studentIds = array_filter($studentIdsRaw, function($id) {
                try {
                    return $id instanceof MongoDB\BSON\ObjectId ? $id : new MongoDB\BSON\ObjectId($id);
                } catch (Exception $e) {
                    error_log("Invalid student ID in class {$class['_id']}: " . $e->getMessage());
                    return false;
                }
            });
            $studentCount = $studentsCollection->countDocuments(['_id' => ['$in' => array_values($studentIds)]]);
        }

        $scheduleParts = explode(' ', $class['schedule'] ?? '');
        $time = count($scheduleParts) > 1 ? implode(' ', array_slice($scheduleParts, 1)) : '--:--';
        if (!$nextClassTime && $time !== '--:--') {
            $nextClassTime = $time;
        }

        $todayClassesFormatted[] = [
            '_id' => (string)$class['_id'],
            'name' => $class['class_name'] ?? 'Unnamed Class',
            'room' => $class['room'] ?? 'Not assigned',
            'studentCount' => $studentCount,
            'time' => $time
        ];
    }

    // Average attendance (this week)
    $attendanceRecords = $attendanceCollection->find([
        'teacherId' => $teacherId,
        'date' => [
            '$gte' => new MongoDB\BSON\UTCDateTime($startOfWeek->getTimestamp() * 1000),
            '$lte' => new MongoDB\BSON\UTCDateTime($endOfWeek->getTimestamp() * 1000)
        ]
    ])->toArray() ?? [];

    $totalAttendance = 0;
    $attendanceCount = 0;
    $chartData = array_fill(0, 5, 0); // Mon-Fri
    $chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

    foreach ($attendanceRecords as $record) {
        $status = $record['status'] ?? 'absent';
        $attendanceValue = $status === 'present' ? 100 : ($status === 'late' ? 50 : 0);
        $totalAttendance += $attendanceValue;
        $attendanceCount++;
        $date = (new DateTime($record['date']->toDateTime()->format('Y-m-d')))->format('D');
        $dayIndex = array_search($date, $chartLabels);
        if ($dayIndex !== false) {
            $chartData[$dayIndex] = ($chartData[$dayIndex] + $attendanceValue) / 2; // Average per day
        }
    }

    $avgAttendance = $attendanceCount > 0 ? round($totalAttendance / $attendanceCount) : 0;

    $result = [
        'totalClasses' => $totalClasses,
        'avgAttendance' => $avgAttendance,
        'classesToday' => count($todayClassesFormatted),
        'nextClassTime' => $nextClassTime ?? '--:--',
        'todayClasses' => $todayClassesFormatted,
        'chartLabels' => $chartLabels,
        'chartData' => $chartData
    ];

    error_log("API teacher dashboard response: " . print_r($result, true));
    echo json_encode($result);

} catch (MongoDB\Driver\Exception\Exception $e) {
    error_log("MongoDB error in api_teacher_dashboard.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'code' => 500]);
} catch (Exception $e) {
    error_log("Error in api_teacher_dashboard.php: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage(), 'code' => $e->getCode() ?: 500]);
}