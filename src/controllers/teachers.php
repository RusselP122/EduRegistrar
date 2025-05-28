<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /EduRegistrar/public/login');
    exit;
}

try {
    $db = getMongoDB();
    $teachers = $db->users->find(['role' => 'teacher'])->toArray();
    $teachers = array_map(function($teacher) {
        return [
            '_id' => (string)$teacher['_id'],
            'name' => $teacher['name'] ?? 'Unknown',
            'email' => $teacher['email'] ?? 'Unknown'
        ];
    }, $teachers);
} catch (Exception $e) {
    error_log("Error fetching teachers: " . $e->getMessage());
    $teachers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teachers - EduRegistrar</title>
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
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Manage Teachers</h2>
            <div class="flex justify-between items-center mb-4">
                <input type="text" id="searchTeachers" placeholder="Search teachers..." 
                       class="w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                <a href="teachers/add" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-user-plus mr-2"></i>Add Teacher
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3 text-left">Name</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="teachersTbody">
                        <?php if (empty($teachers)): ?>
                            <tr><td colspan="3" class="p-3 text-center text-gray-600">No teachers found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($teachers as $teacher): ?>
                                <tr class="border-b">
                                    <td class="p-3"><?php echo htmlspecialchars($teacher['name']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($teacher['email']); ?></td>
                                    <td class="p-3">
                                        <a href="teachers/edit/<?php echo $teacher['_id']; ?>" class="text-indigo-600 hover:text-indigo-500">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="teachers/delete/<?php echo $teacher['_id']; ?>" class="text-red-600 hover:text-red-500 ml-2"
                                           onclick="return confirm('Are you sure you want to delete this teacher?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-indigo-600 text-white text-center p-4">
        <p>© 2025 EduRegistrar. All rights reserved.</p>
    </footer>

    <script>
        document.getElementById('searchTeachers')?.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#teachersTbody tr');
            rows.forEach(row => {
                if (row.cells.length < 3) return;
                const name = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                row.style.display = (name.includes(query) || email.includes(query)) ? '' : 'none';
            });
        });
    </script>
</body>
</html>