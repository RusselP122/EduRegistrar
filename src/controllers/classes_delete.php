<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /EduRegistrar/public/login');
    exit;
}

$classId = $_GET['id'] ?? '';
if (!$classId) {
    $_SESSION['error'] = 'Class ID is required';
    header('Location: /EduRegistrar/public/classes');
    exit;
}

try {
    $db = getMongoDB();
    $classCollection = $db->classes;
    $objectId = new MongoDB\BSON\ObjectId($classId);

    $result = $classCollection->deleteOne(['_id' => $objectId]);

    if ($result->getDeletedCount() > 0) {
        header('Location: /EduRegistrar/public/classes');
        exit;
    } else {
        $_SESSION['error'] = 'Class not found';
        header('Location: /EduRegistrar/public/classes');
        exit;
    }
} catch (MongoDB\Driver\Exception\Exception $e) {
    error_log("MongoDB error in classes_delete.php: " . $e->getMessage());
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header('Location: /EduRegistrar/public/classes');
    exit;
} catch (Exception $e) {
    error_log("Error in classes_delete.php: " . $e->getMessage());
    $_SESSION['error'] = 'Invalid class ID';
    header('Location: /EduRegistrar/public/classes');
    exit;
}