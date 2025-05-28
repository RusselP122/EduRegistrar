<?php
require_once __DIR__ . '/../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

$id = $_GET['id'];
$db = getMongoDB();
$teacher = $db->users->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $db->users->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($id)],
        ['$set' => ['name' => $name, 'email' => $email]]
    );
    header('Location: /teachers');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Teacher</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <base href="/EduRegistrar/public/">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center">Edit Teacher</h2>
        <form action="/teachers/edit/<?php echo $id; ?>" method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium">Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($teacher['name']); ?>" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" class="w-full p-2 border rounded" required>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white p-2 rounded hover:bg-indigo-700">Update Teacher</button>
        </form>
    </div>
</body>
</html>