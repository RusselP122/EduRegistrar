<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /EduRegistrar/public/login');
    exit;
}

$db = getMongoDB();
$classCollection = $db->classes;
$studentCollection = $db->students;
$usersCollection = $db->users;

$classId = $_GET['id'] ?? '';
if (!$classId) {
    $_SESSION['error'] = 'Class ID is required';
    header('Location: /EduRegistrar/public/classes');
    exit;
}

try {
    $classObjectId = new MongoDB\BSON\ObjectId($classId);
    $class = $classCollection->findOne(['_id' => $classObjectId]);
    if (!$class) {
        $_SESSION['error'] = 'Class not found';
        header('Location: /EduRegistrar/public/classes');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Invalid class ID';
    header('Location: /EduRegistrar/public/classes');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $className = trim($_POST['class_name'] ?? '');
        $studentIds = isset($_POST['students']) && is_array($_POST['students']) ? $_POST['students'] : [];
        $teacherId = trim($_POST['teacher'] ?? '');

        error_log("Class edit attempt: class_id=$classId, class_name=$className, teacher=$teacherId, students=" . print_r($studentIds, true));

        if (empty($className)) {
            $_SESSION['error'] = 'Class name is required';
            header('Location: /EduRegistrar/public/classes/edit/' . $classId);
            exit;
        }

        $validStudentIds = [];
        if (!empty($studentIds)) {
            foreach ($studentIds as $id) {
                try {
                    $objectId = new MongoDB\BSON\ObjectId($id);
                    if ($studentCollection->findOne(['_id' => $objectId])) {
                        $validStudentIds[] = $objectId;
                    } else {
                        error_log("Invalid student ID: $id");
                    }
                } catch (Exception $e) {
                    error_log("Invalid ObjectId format for student: $id");
                }
            }
        }

        $teacher = null;
        if (!empty($teacherId)) {
            try {
                $teacherObjectId = new MongoDB\BSON\ObjectId($teacherId);
                if ($usersCollection->findOne(['_id' => $teacherObjectId, 'role' => 'teacher'])) {
                    $teacher = $teacherObjectId;
                } else {
                    error_log("Invalid teacher ID: $teacherId");
                }
            } catch (Exception $e) {
                error_log("Invalid ObjectId format for teacher: $teacherId");
            }
        }

        $result = $classCollection->updateOne(
            ['_id' => $classObjectId],
            ['$set' => [
                'class_name' => $className,
                'students' => $validStudentIds,
                'teacher' => $teacher
            ]]
        );

        if ($result->getModifiedCount() > 0 || $result->getMatchedCount() > 0) {
            header('Location: /EduRegistrar/public/classes');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to update class';
            header('Location: /EduRegistrar/public/classes/edit/' . $classId);
            exit;
        }
    } catch (MongoDB\Driver\Exception\Exception $e) {
        error_log("MongoDB error in classes_edit.php: " . $e->getMessage());
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header('Location: /EduRegistrar/public/classes/edit/' . $classId);
        exit;
    } catch (Exception $e) {
        error_log("Error in classes_edit.php: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header('Location: /EduRegistrar/public/classes/edit/' . $classId);
        exit;
    }
}

// Fetch all students and teachers for dropdowns
$students = $studentCollection->find()->toArray();
$teachers = $usersCollection->find(['role' => 'teacher'])->toArray();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduRegistrar - Edit Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <base href="/EduRegistrar/public/">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Header -->
    <header class="bg-indigo-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-graduation-cap text-2xl mr-2"></i>
                <h1 class="text-xl font-bold">EduRegistrar - Edit Class</h1>
            </div>
            <nav class="flex items-center space-x-4">
                <a href="dashboard" class="hover:text-indigo-200"><i class="fas fa-home mr-1"></i>Dashboard</a>
                <a href="teachers" class="hover:text-indigo-200"><i class="fas fa-chalkboard-teacher mr-1"></i>Teachers</a>
                <a href="students" class="hover:text-indigo-200"><i class="fas fa-user-graduate mr-1"></i>Students</a>
                <a href="classes" class="hover:text-indigo-200"><i class="fas fa-book mr-1"></i>Classes</a>
                <a href="reports" class="hover:text-indigo-200"><i class="fas fa-chart-bar mr-1"></i>Reports</a>
                <a href="logout" class="hover:text-indigo-200"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Edit Class</h2>
            <p class="text-gray-600 mb-6">Update class details, assign students and teacher.</p>

            <!-- Display Error Message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Edit Class Form -->
            <form action="classes/edit/<?php echo htmlspecialchars($classId); ?>" method="POST" class="p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label for="className" class="block text-gray-700">Class Name</label>
                        <input type="text" id="className" name="class_name" value="<?php echo htmlspecialchars($class['class_name'] ?? ''); ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="classTeacher" class="block text-gray-700">Assign Teacher</label>
                        <select id="classTeacher" name="teacher" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">No Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo (string)$teacher['_id']; ?>" <?php echo isset($class['teacher']) && (string)$class['teacher'] === (string)$teacher['_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($teacher['name'] . ' (' . $teacher['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="classStudents" class="block text-gray-700">Assign Students</label>
                        <select multiple id="classStudents" name="students[]" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500" size="5">
                            <?php
                            // Convert BSONArray to PHP array for students
                            $classStudents = isset($class['students']) ? (array)$class['students'] : [];
                            foreach ($students as $student):
                            ?>
                                <option value="<?php echo (string)$student['_id']; ?>" <?php echo in_array((string)$student['_id'], array_map('strval', $classStudents)) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($student['name'] . ' (' . $student['email'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save Changes</button>
                    <a href="classes" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-indigo-600 text-white text-center p-4">
        <p>© 2025 EduRegistrar. All rights reserved.</p>
    </footer>
</body>
</html>