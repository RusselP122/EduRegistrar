<?php
require_once __DIR__ . '/../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

$id = $_GET['id'];
$db = getMongoDB();
$db->users->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
header('Location: /teachers');
exit;
?>