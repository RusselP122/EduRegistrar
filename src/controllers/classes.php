<?php
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
    <title>EduRegistrar - Manage Classes</title>
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
                <h1 class="text-xl font-bold">EduRegistrar - Admin Classes</h1>
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
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Manage Classes</h2>
            <p class="text-gray-600 mb-6">Add, edit, or delete classes, assign students and teachers.</p>

            <!-- Display Error Message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Add Class -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <input type="text" id="searchClasses" placeholder="Search classes..." class="w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <button onclick="toggleClassForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-book mr-2"></i>Add Class
                    </button>
                </div>
                <!-- Add Class Form -->
                <form id="addClassForm" class="hidden mb-4 p-4 bg-gray-50 rounded-lg" action="classes/add" method="POST">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="className" class="block text-gray-700">Class Name</label>
                            <input type="text" id="className" name="class_name" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="classTeacher" class="block text-gray-700">Assign Teacher</label>
                            <select id="classTeacher" name="teacher" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">No Teacher</option>
                                <!-- Populated dynamically via JavaScript -->
                            </select>
                        </div>
                        <div>
                            <label for="classStudents" class="block text-gray-700">Assign Students</label>
                            <select multiple id="classStudents" name="students[]" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <!-- Populated dynamically via JavaScript -->
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save Class</button>
                        <button type="button" onclick="toggleClassForm()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Classes Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3 text-left">Class Name</th>
                            <th class="p-3 text-left">Teacher</th>
                            <th class="p-3 text-left">Students</th>
                            <th class="p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="classesTbody">
                        <tr><td colspan="4" class="p-3 text-center text-gray-600">Loading classes...</td></tr>
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
        // Toggle Class Form
        function toggleClassForm() {
            const form = document.getElementById('addClassForm');
            form.classList.toggle('hidden');
            if (!form.classList.contains('hidden')) {
                populateStudentsDropdown();
                populateTeachersDropdown();
            }
        }

        // Populate Students Dropdown
        async function populateStudentsDropdown() {
            const select = document.getElementById('classStudents');
            try {
                const response = await fetch('api/students', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const students = await response.json();
                select.innerHTML = students.length > 0
                    ? students.map(student => `
                        <option value="${student._id}">${student.name} (${student.email})</option>
                    `).join('')
                    : `<option>No students available</option>`;
            } catch (error) {
                console.error('Error fetching students for dropdown:', error);
                select.innerHTML = `<option>Error loading students</option>`;
            }
        }

        // Populate Teachers Dropdown
        async function populateTeachersDropdown() {
            const select = document.getElementById('classTeacher');
            try {
                const response = await fetch('api/teachers', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const teachers = await response.json();
                select.innerHTML = `<option value="">No Teacher</option>` + 
                    (teachers.length > 0
                        ? teachers.map(teacher => `
                            <option value="${teacher._id}">${teacher.name} (${teacher.email})</option>
                        `).join('')
                        : `<option>No teachers available</option>`);
            } catch (error) {
                console.error('Error fetching teachers for dropdown:', error);
                select.innerHTML = `<option value="">Error loading teachers</option>`;
            }
        }

        // Fetch Classes
        async function fetchClasses() {
            const tbody = document.querySelector('#classesTbody');
            try {
                const response = await fetch('api/classes', {
                    headers: { 'Accept': 'application/json' }
                });
                console.log('HTTP Status:', response.status, response.statusText);
                const rawResponse = await response.text();
                console.log('Raw response from /api/classes:', rawResponse);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status} ${response.statusText}`);
                }
                try {
                    const classes = JSON.parse(rawResponse);
                    if (!Array.isArray(classes)) {
                        throw new Error('Response is not an array');
                    }
                    if (classes.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="p-3 text-center text-gray-600">
                                    No classes found
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    tbody.innerHTML = classes.map(cls => `
                        <tr class="border-b">
                            <td class="p-3">${cls.class_name || 'Unnamed Class'}</td>
                            <td class="p-3">${cls.teacher ? cls.teacher.name : 'No Teacher'}</td>
                            <td class="p-3">${cls.students && Array.isArray(cls.students) ? cls.students.map(s => s.name || 'Unknown').join(', ') : 'No students'}</td>
                            <td class="p-3">
                                <a href="classes/edit/${cls._id || ''}" class="text-indigo-600 hover:text-indigo-500">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="classes/delete/${cls._id || ''}" class="text-red-600 hover:text-red-500 ml-2">
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
                        <td colspan="4" class="p-3 text-center text-red-600">
                            Error loading classes: ${error.message}
                        </td>
                    </tr>
                `;
            }
        }

        // Handle form submission
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, setting up form handler');
            const form = document.getElementById('addClassForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    console.log('Form submission triggered');
                    const formData = new FormData(form);
                    console.log('Form data:', Object.fromEntries(formData));
                    try {
                        const response = await fetch(form.action, {
                            method: form.method,
                            body: formData,
                            headers: { 'Accept': 'application/json' }
                        });
                        console.log('Form submission response:', response.status, response.statusText);
                        const data = await response.json();
                        if (response.ok && data.success) {
                            form.reset();
                            form.classList.add('hidden');
                            fetchClasses();
                            alert('Class added successfully');
                        } else {
                            console.error('Form submission error:', data.error);
                            alert('Failed to add class: ' + (data.error || 'Unknown error'));
                        }
                    } catch (error) {
                        console.error('Submission error:', error);
                        alert('Error submitting form: ' + error.message);
                    }
                });
            } else {
                console.error('addClassForm not found');
            }
            console.log('DOM loaded, fetching classes');
            fetchClasses();
        });

        // Search Classes
        document.getElementById('searchClasses').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#classesTbody tr');
            rows.forEach(row => {
                if (row.cells.length < 4) return;
                const className = row.cells[0].textContent.toLowerCase();
                const teacher = row.cells[1].textContent.toLowerCase();
                const students = row.cells[2].textContent.toLowerCase();
                row.style.display = (className.includes(query) || teacher.includes(query) || students.includes(query)) ? '' : 'none';
            });
        });
    </script>
</body>
</html>