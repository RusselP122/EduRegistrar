<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    header('Location: /EduRegistrar/public/');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduRegistrar - Dashboard</title>
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
                <h1 class="text-xl font-bold">EduRegistrar - <?php echo $_SESSION['role'] === 'admin' ? 'Admin' : 'Teacher'; ?> Dashboard</h1>
            </div>
            <nav class="flex items-center space-x-4">
                <a href="dashboard" class="hover:text-indigo-200"><i class="fas fa-home mr-1"></i>Dashboard</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="teachers" class="hover:text-indigo-200"><i class="fas fa-chalkboard-teacher mr-1"></i>Teachers</a>
                    <a href="students" class="hover:text-indigo-200"><i class="fas fa-user-graduate mr-1"></i>Students</a>
                    <a href="classes" class="hover:text-indigo-200"><i class="fas fa-book mr-1"></i>Classes</a>
                <?php endif; ?>
                <a href="reports" class="hover:text-indigo-200"><i class="fas fa-chart-bar mr-1"></i>Reports</a>
                <a href="logout" class="hover:text-indigo-200"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
            <p class="text-gray-600 mb-6">
                <?php echo $_SESSION['role'] === 'admin' ? 'Manage teachers, students, classes, and generate attendance reports from here.' : 'View attendance reports here.'; ?>
            </p>

            <!-- Display Error Message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Manage Teachers (Admin Only) -->
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Manage Teachers</h3>
                <div class="flex justify-between items-center mb-4">
                    <input type="text" id="searchTeachers" placeholder="Search teachers..." class="w-1/3 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <a href="teachers/add" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-user-plus mr-2"></i>Add Teacher
                    </a>
                </div>
                <div id="teachersTable" class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-3 text-left">Name</th>
                                <th class="p-3 text-left">Email</th>
                                <th class="p-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="teachersTbody">
                            <tr><td colspan="3" class="p-3 text-center text-gray-600">Loading teachers...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

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

            <!-- Manage Classes (Admin Only) -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Manage Classes</h3>
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
                            <select multiple id="classStudents" name="students[]" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500" size="5">
                                <!-- Populated dynamically via JavaScript -->
                            </select>
                            <p class="text-sm text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple students.</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Save Class</button>
                        <button type="button" onclick="toggleClassForm()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">Cancel</button>
                    </div>
                </form>
                <div id="classesTable" class="overflow-x-auto">
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
            <?php endif; ?>

            <!-- Attendance Reports -->
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Attendance Reports</h3>
                <div class="flex space-x-4 mb-4">
                    <button onclick="generateReport('daily')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-chart-bar mr-2"></i>Daily Report
                    </button>
                    <button onclick="generateReport('weekly')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-chart-bar mr-2"></i>Weekly Report
                    </button>
                </div>
                <div id="reportsOutput" class="bg-gray-100 p-4 rounded-lg">
                    <p class="text-gray-600">Click a report button to view attendance data.</p>
                </div>
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

        // Toggle Class Form
        function toggleClassForm() {
            const form = document.getElementById('addClassForm');
            form.classList.toggle('hidden');
            if (!form.classList.contains('hidden')) {
                populateStudentsDropdown();
                populateTeachersDropdown();
            }
        }

        // Update Section dropdown based on Year selection
        document.getElementById('studentYear')?.addEventListener('change', function(e) {
            const year = e.target.value;
            const sectionSelect = document.getElementById('studentSection');
            sectionSelect.innerHTML = '<option value="" disabled selected>Select Section</option>';

            if (year === '1st Year' || year === '2nd Year') {
                sectionSelect.innerHTML += '<option value="D">D</option>';
            } else if (year === '3rd Year' || year === '4th Year') {
                sectionSelect.innerHTML += `
                    <option value="SMP-D">Service Management Program (SMP) - D</option>
                    <option value="WMAD-D">Web and Mobile Application Development (WMAD) - D</option>
                `;
            }
        });

        // Populate Students Dropdown for Class Form
        async function populateStudentsDropdown() {
            const select = document.getElementById('classStudents');
            try {
                const response = await fetch('api/students', {
                    headers: { 'Accept': 'application/json' }
                });
                console.log('Students dropdown response:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const students = await response.json();
                console.log('Students for dropdown:', students);
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

        // Populate Teachers Dropdown for Class Form
        async function populateTeachersDropdown() {
            const select = document.getElementById('classTeacher');
            try {
                const response = await fetch('api/teachers', {
                    headers: { 'Accept': 'application/json' }
                });
                console.log('Teachers dropdown response:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const teachers = await response.json();
                console.log('Teachers for dropdown:', teachers);
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

        // Fetch Teachers (Admin Only)
        <?php if ($_SESSION['role'] === 'admin'): ?>
        async function fetchTeachers() {
            const tbody = document.querySelector('#teachersTbody');
            try {
                const response = await fetch('api/teachers', {
                    headers: { 'Accept': 'application/json' }
                });
                console.log('HTTP Status (teachers):', response.status, response.statusText);
                const rawResponse = await response.text();
                console.log('Raw response from /api/teachers:', rawResponse);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status} ${response.statusText}`);
                }
                try {
                    const teachers = JSON.parse(rawResponse);
                    if (!Array.isArray(teachers)) {
                        throw new Error('Response is not an array');
                    }
                    if (teachers.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="3" class="p-3 text-center text-gray-600">
                                    No teachers found
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    tbody.innerHTML = teachers.map(teacher => `
                        <tr class="border-b">
                            <td class="p-3">${teacher.name || 'Unknown'}</td>
                            <td class="p-3">${teacher.email || 'Unknown'}</td>
                            <td class="p-3">
                                <a href="teachers/edit/${teacher._id || ''}" class="text-indigo-600 hover:text-indigo-500">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="teachers/delete/${teacher._id || ''}" class="text-red-600 hover:text-red-500 ml-2">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    `).join('');
                } catch (parseError) {
                    console.error('Invalid JSON (teachers):', rawResponse);
                    throw new Error(`Invalid response: ${rawResponse.substring(0, 100)}...`);
                }
            } catch (error) {
                console.error('Fetch error (teachers):', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="p-3 text-center text-red-600">
                            Error loading teachers: ${error.message}
                        </td>
                    </tr>
                `;
            }
        }

        // Fetch Students (Admin Only)
        async function fetchStudents() {
            const tbody = document.querySelector('#studentsTbody');
            try {
                const response = await fetch('api/students', {
                    headers: { 'Accept': 'application/json' }
                });
                console.log('HTTP Status (students):', response.status, response.statusText);
                const rawResponse = await response.text();
                console.log('Raw response from /api/students:', rawResponse);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status} ${response.statusText}`);
                }
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
                                <a href="students/edit/${student._id || ''}" class="text-indigo-600 hover:text-indigo-500">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="students/delete/${student._id || ''}" class="text-red-600 hover:text-red-500 ml-2">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    `).join('');
                } catch (parseError) {
                    console.error('Invalid JSON (students):', rawResponse);
                    throw new Error(`Invalid response: ${rawResponse.substring(0, 100)}...`);
                }
            } catch (error) {
                console.error('Fetch error (students):', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="p-3 text-center text-red-600">
                            Error loading students: ${error.message}
                        </td>
                    </tr>
                `;
            }
        }

        // Fetch Classes (Admin Only)
        async function fetchClasses() {
            const tbody = document.querySelector('#classesTbody');
            try {
                const response = await fetch('api/classes', {
                    headers: { 'Accept': 'application/json' }
                });
                console.log('HTTP Status (classes):', response.status, response.statusText);
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
                    tbody.innerHTML = classes.map(cls => {
                        console.log('Class students:', cls.students);
                        const studentNames = Array.isArray(cls.students) && cls.students.length > 0
                            ? cls.students.map(s => s.name || 'Unknown').filter(name => name !== 'Unknown').join(', ')
                            : 'No students';
                        return `
                        <tr class="border-b">
                            <td class="p-3">${cls.class_name || 'Unnamed Class'}</td>
                            <td class="p-3">${cls.teacher && cls.teacher.name ? cls.teacher.name : 'No Teacher'}</td>
                            <td class="p-3">${studentNames}</td>
                            <td class="p-3">
                                <a href="classes/edit/${cls._id || ''}" class="text-indigo-600 hover:text-indigo-500">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="classes/delete/${cls._id || ''}" class="text-red-600 hover:text-red-500 ml-2">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        `;
                    }).join('');
                } catch (parseError) {
                    console.error('Invalid JSON (classes):', rawResponse);
                    throw new Error(`Invalid response: ${rawResponse.substring(0, 100)}...`);
                }
            } catch (error) {
                console.error('Fetch error (classes):', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="p-3 text-center text-red-600">
                            Error loading classes: ${error.message}
                        </td>
                    </tr>
                `;
            }
        }

        // Handle Class Form Submission (Admin Only)
        document.getElementById('addClassForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('Class form submission triggered');
            const form = e.target;
            const formData = new FormData(form);
            const students = formData.getAll('students[]');
            console.log('Form data:', Object.fromEntries(formData));
            console.log('Selected students:', students);
            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });
                console.log('Form submission response:', response.status, response.statusText);
                const rawResponse = await response.text();
                console.log('Raw response from /classes/add:', rawResponse);
                try {
                    const data = JSON.parse(rawResponse);
                    if (response.ok && data.success) {
                        form.reset();
                        form.classList.add('hidden');
                        fetchClasses();
                        alert('Class added successfully');
                    } else {
                        console.error('Form submission error:', data.error);
                        alert('Failed to add class: ' + (data.error || 'Unknown error'));
                    }
                } catch (parseError) {
                    console.error('Invalid JSON from /classes/add:', rawResponse);
                    alert('Error: Server returned invalid response: ' + rawResponse.substring(0, 100));
                    throw new Error(`Invalid JSON: ${rawResponse.substring(0, 100)}...`);
                }
            } catch (error) {
                console.error('Submission error:', error);
                alert('Error submitting form: ' + error.message);
            }
        });

        // Search Teachers (Admin Only)
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

        // Search Students (Admin Only)
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

        // Search Classes (Admin Only)
        document.getElementById('searchClasses')?.addEventListener('input', function(e) {
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
        <?php endif; ?>

        // Generate Reports
        async function generateReport(type) {
            const output = document.getElementById('reportsOutput');
            output.innerHTML = `<p>Loading ${type} attendance report...</p>`;
            try {
                const response = await fetch(`/api/reports?type=${type}`, {
                    headers: { 'Accept': 'application/json' }
                });
                console.log('HTTP Status (reports):', response.status, response.statusText);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                if (data.length === 0) {
                    output.innerHTML = `<p>No ${type} attendance data available.</p>`;
                    return;
                }
                output.innerHTML = `
                    <p>${type.charAt(0).toUpperCase() + type.slice(1)} Report:</p>
                    <ul class="list-disc pl-5">
                        ${data.map(record => `<li>${record.course_name}: ${record.status} on ${new Date(record.date).toLocaleDateString()}</li>`).join('')}
                    </ul>
                `;
            } catch (error) {
                console.error(`Error fetching ${type} report:`, error);
                output.innerHTML = `<p class="text-red-600">Failed to load ${type} report. Please try again.</p>`;
            }
        }

        // Load data on page load
        document.addEventListener('DOMContentLoaded', () => {
            <?php if ($_SESSION['role'] === 'admin'): ?>
            fetchTeachers();
            fetchStudents();
            fetchClasses();
            <?php endif; ?>
        });
    </script>
</body>
</html>