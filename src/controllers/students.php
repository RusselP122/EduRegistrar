<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /EduRegistrar/public/login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - EduRegistrar</title>
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
                <a href="students" class="hover:text-indigo-200"><i class="fas fa-user-graduate mr-1"></i>Students</a>
                <a href="classes" class="hover:text-indigo-200"><i class="fas fa-book mr-1"></i>Classes</a>
                <a href="reports" class="hover:text-indigo-200"><i class="fas fa-chart-bar mr-1"></i>Reports</a>
                <a href="logout" class="hover:text-indigo-200"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-6 flex-grow">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Manage Students</h2>
            <!-- Manage Students (Admin Only) -->
            <div class="mb-8">
    <h3 class="text-xl font-semibold text-gray-800 mb-4">Manage Students</h3>
    <div class="flex justify-between items-center mb-4">
        <input type="text" id="searchStudents" placeholder="Search students..." class="w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
        <button onclick="toggleStudentForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-user-plus mr-2"></i>Add Student
        </button>
    </div>
    <!-- Add Student Form -->
    <form id="addStudentForm" class="hidden mb-4 p-4 bg-gray-50 rounded-lg" action="students/add" method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="studentName" class="block text-gray-700">Name</label>
                <input type="text" id="studentName" name="name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="studentEmail" class="block text-gray-700">Email</label>
                <input type="email" id="studentEmail" name="email" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="studentPassword" class="block text-gray-700">Password</label>
                <input type="password" id="studentPassword" name="password" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="studentYear" class="block text-gray-700">Year</label>
                <select id="studentYear" name="year" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="" disabled selected>Select Year</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
            <div>
                <label for="studentSection" class="block text-gray-700">Section</label>
                <select id="studentSection" name="section" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="" disabled selected>Select Section</option>
                    <!-- Options populated dynamically via JavaScript -->
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save Student</button>
            <button type="button" onclick="toggleStudentForm()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Cancel</button>
        </div>
    </form>
    <div id="studentsTable" class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Year</th>
                    <th class="p-3 text-left">Section</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="studentsTbody">
                <tr><td colspan="5" class="p-3 text-center text-gray-600">Loading students...</td></tr>
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
        // Toggle Student Form
        function toggleStudentForm() {
            const form = document.getElementById('addStudentForm');
            form.classList.toggle('hidden');
        }

        // Fetch Students (Admin Only)
        async function fetchStudents() {
            const tbody = document.querySelector('#studentsTbody');
            try {
                const response = await fetch('api/students', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const rawResponse = await response.text();
                console.log('Raw response from /api/students:', rawResponse); // Debug log
                try {
                    const students = JSON.parse(rawResponse);
                    if (!Array.isArray(students)) {
                        throw new Error('Response is not an array');
                    }
                    if (students.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="5" class="p-3 text-center text-gray-600">
                                    No students found
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    tbody.innerHTML = students.map(student => `
                        <tr class="border-b">
                            <td class="p-3">${student.name || 'Unknown'}</td>
                            <td class="p-3">${student.email || 'Unknown'}</td>
                            <td class="p-3">${student.year || 'Unknown'}</td>
                            <td class="p-3">${student.section || 'Unknown'}</td>
                            <td class="p-3">
                                <a href="students/edit/${student._id}" class="text-indigo-600 hover:text-indigo-500">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="students/delete/${student._id}" class="text-red-600 hover:text-red-500 ml-2"
                                   onclick="return confirm('Are you sure you want to delete this student?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    `).join('');
                } catch (parseError) {
                    console.error('Invalid JSON:', rawResponse);
                    throw new Error(`Invalid response: ${rawResponse.substring(0, 100)}...`);
                }
            } catch (error) {
                console.error('Fetch error:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="p-3 text-center text-red-600">
                            Error loading students: ${error.message}
                        </td>
                    </tr>
                `;
            }
        }

        // Search Students
        document.getElementById('searchStudents')?.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#studentsTbody tr');
            rows.forEach(row => {
                if (row.cells.length < 5) return;
                const name = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                const year = row.cells[2].textContent.toLowerCase();
                const section = row.cells[3].textContent.toLowerCase();
                row.style.display = (name.includes(query) || email.includes(query) || year.includes(query) || section.includes(query)) ? '' : 'none';
            });
        });

        // Load students on page load
        document.addEventListener('DOMContentLoaded', () => {
            fetchStudents();
        });
    </script>
</body>
</html>