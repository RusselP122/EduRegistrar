
<?php
require_once __DIR__ . '/../config/db.php';

// Check admin access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /EduRegistrar/public/login');
    exit;
}

// Initialize variables
$errors = [];
$success = '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

// CSRF token - Generate only if not exists or on GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET' || !isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token';
    }

    // Validate inputs
    $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Name must be 100 characters or less';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }

    // Check email uniqueness
    if (empty($errors)) {
        try {
            $db = getMongoDB();
            $existingUser = $db->users->findOne(['email' => $email]);
            if ($existingUser) {
                $errors[] = 'Email already exists';
            }
        } catch (Exception $e) {
            error_log("Error checking email: " . $e->getMessage());
            $errors[] = 'Database error. Please try again.';
        }
    }

    // Insert teacher if no errors
    if (empty($errors)) {
        try {
            $password = password_hash($password, PASSWORD_BCRYPT);
            $db->users->insertOne([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'teacher',
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]);
            $success = 'Teacher added successfully';
            // Clear form
            $name = $email = '';
            // Generate new CSRF token after successful submission
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $csrf_token = $_SESSION['csrf_token'];
            // Redirect to /teachers after 2 seconds
            header('Refresh: 2; URL=/EduRegistrar/public/teachers');
        } catch (Exception $e) {
            error_log("Error adding teacher: " . $e->getMessage());
            $errors[] = 'Failed to add teacher. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher - EduRegistrar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <base href="/EduRegistrar/public/">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-indigo-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-graduation-cap text-2xl mr-2"></i>
                <h1 class="text-xl font-bold">EduRegistrar - Admin</h1>
            </div>
            <nav class="flex items-center space-x-4">
                <a href="dashboard" class="hover:text-indigo-200"><i class="fas fa-home mr-1"></i>Dashboard</a>
                <a href="teachers" class="hover:text-indigo-200"><i class="fas fa-chalkboard-teacher mr-1"></i>Teachers</a>
                <a href="reports" class="hover:text-indigo-200"><i class="fas fa-chart-bar mr-1"></i>Reports</a>
                <a href="logout" class="hover:text-indigo-200"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-6 flex-grow">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md mx-auto">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Add Teacher</h2>

            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                    <?php echo htmlspecialchars($success); ?>
                    <p>Redirecting to teachers list...</p>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                    <ul class="list-disc pl-5">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="teachers/add" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" 
                           class="w-full p-2 border rounded focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                           class="w-full p-2 border rounded focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" 
                           class="w-full p-2 border rounded focus:ring-2 focus:ring-indigo-500" required>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white p-2 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-user-plus mr-2"></i>Add Teacher
                </button>
            </form>
            <a href="teachers" class="block text-center mt-4 text-indigo-600 hover:text-indigo-500">Back to Teachers</a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-indigo-600 text-white text-center p-4">
        <p>© 2025 EduRegistrar. All rights reserved.</p>
    </footer>
</body>
</html>