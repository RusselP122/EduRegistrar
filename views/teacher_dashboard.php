<?php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header('Location: /EduRegistrar/public/');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduRegistrar - Teacher Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <base href="/EduRegistrar/public/">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-600 w-10 h-10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">EduRegistrar</h1>
                        <p class="text-sm text-gray-600">Teacher Dashboard</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button onclick="toggleNotifications()" class="flex items-center space-x-2 text-gray-700 hover:text-indigo-600">
                            <i class="fas fa-bell text-lg"></i>
                            <span id="notificationCount" class="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                        </button>
                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl p-4 z-10">
                            <p class="text-sm text-gray-600">No notifications</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <img src="https://via.placeholder.com/40" alt="Teacher" class="w-10 h-10 rounded-full">
                        <div>
                            <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Teacher'); ?></p>
                            <p class="text-xs text-gray-600">Teacher</p>
                        </div>
                        <button onclick="toggleDropdown()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl p-4 z-10">
                        <a href="#profile" onclick="showSection(event, 'profile')" class="block text-sm text-gray-700 hover:text-indigo-600 mb-2">Profile</a>
                        <a href="logout" class="block text-sm text-red-600 hover:text-red-500">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <aside class="w-64 bg-white shadow-lg h-screen sticky top-0">
            <div class="p-6">
                <nav class="space-y-2">
                    <a href="#dashboard" onclick="showSection(event, 'dashboard')" class="nav-link active flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="#classes" onclick="showSection(event, 'classes')" class="nav-link flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-chalkboard"></i>
                        <span>My Classes</span>
                    </a>
                    <a href="#attendance" onclick="showSection(event, 'attendance')" class="nav-link flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Take Attendance</span>
                    </a>
                    <a href="#reports" onclick="showSection(event, 'reports')" class="nav-link flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                    <a href="#schedule" onclick="showSection(event, 'schedule')" class="nav-link flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-calendar"></i>
                        <span>Schedule</span>
                    </a>
                    <a href="#profile" onclick="showSection(event, 'profile')" class="nav-link flex items-center space-x-3 text-gray-700 p-3 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition duration-200">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="logout" class="flex items-center space-x-3 text-red-600 p-3 rounded-lg hover:bg-red-50 transition duration-200">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </aside>

        <main class="flex-1 p-6">
            <div id="dashboard" class="section active">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl text-white p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-2">Good Morning, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Teacher'); ?>!</h2>
                    <p class="text-indigo-100">You have <span id="todayClassesCount">0</span> classes scheduled for today. Here's your overview.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Classes</p>
                                <p class="text-3xl font-bold text-gray-900" id="totalClasses">0</p>
                                <p class="text-sm text-blue-600 mt-1">Active this semester</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="fas fa-chalkboard text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Avg. Attendance</p>
                                <p class="text-3xl font-bold text-gray-900" id="avgAttendance">N/A</p>
                                <p class="text-sm text-yellow-600 mt-1">This week</p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-full">
                                <i class="fas fa-clipboard-check text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Classes Today</p>
                                <p class="text-3xl font-bold text-gray-900" id="classesToday">0</p>
                                <p class="text-sm text-purple-600 mt-1">Next at <span id="nextClassTime">--:--</span></p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="fas fa-clock text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Today's Classes</h3>
                    <div id="todayClasses" class="space-y-4">
                        <p class="text-gray-600 text-center">Loading classes...</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Weekly Attendance Overview</h3>
                    <p class="text-gray-600">Attendance data not available.</p>
                </div>
            </div>
            <div id="classes" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">My Classes</h2>
                <div id="classesList" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <p class="text-gray-600 text-center">Loading classes...</p>
                </div>
            </div>
            <div id="attendance" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Take Attendance</h2>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <select id="classSelect" class="w-full p-3 border rounded-lg mb-4">
                        <option value="">Select a class</option>
                    </select>
                    <div id="attendanceSummary" class="mb-4 hidden">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Attendance Summary</h3>
                        <div class="flex space-x-4">
                            <p>Present: <span id="presentCount" class="font-bold">0</span></p>
                            <p>Absent: <span id="absentCount" class="font-bold">0</span></p>
                            <p>Late: <span id="lateCount" class="font-bold">0</span></p>
                        </div>
                    </div>
                    <div id="attendanceForm" class="space-y-4">
                        <p class="text-gray-600">Select a class to take attendance.</p>
                    </div>
                    <button onclick="saveAttendance()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 mt-4" disabled id="saveAttendanceBtn">
                        Save Attendance
                    </button>
                </div>
            </div>
            <div id="reports" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Attendance Reports</h2>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <select id="reportType" class="w-full p-3 border rounded-lg mb-4">
                        <option value="">Select report type</option>
                        <option value="daily">Daily Report</option>
                        <option value="weekly">Weekly Report</option>
                    </select>
                    <div id="reportsOutput" class="space-y-4">
                        <p class="text-gray-600">Select a report type to view data.</p>
                    </div>
                </div>
            </div>
            <div id="schedule" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Schedule</h2>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div id="scheduleList" class="space-y-4">
                        <p class="text-gray-600">No schedule available.</p>
                    </div>
                </div>
            </div>
            <div id="profile" class="section hidden">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Profile</h2>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <form id="profileForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>" class="w-full p-3 border rounded-lg" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" class="w-full p-3 border rounded-lg" required>
                        </div>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Update Profile</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <footer class="bg-indigo-600 text-white text-center p-4">
        <p>© 2025 EduRegistrar. All rights reserved.</p>
    </footer>

    <script>
    function toggleDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('hidden');
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('hidden');
    }

    function showSection(event, sectionId) {
        if (event) event.preventDefault();
        document.querySelectorAll('.section').forEach(section => section.classList.add('hidden'));
        document.getElementById(sectionId).classList.remove('hidden');
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        document.querySelector(`a[href="#${sectionId}"]`).classList.add('active');
    }

    async function fetchDashboard() {
        try {
            const response = await fetch('api/teacher/dashboard', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });
            console.log('HTTP Status (dashboard):', response.status, response.statusText);
            const text = await response.text();
            console.log('Raw response from /api/teacher/dashboard:', text);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} ${response.statusText}`);
            }
            const data = JSON.parse(text);
            console.log('Dashboard data:', data);

            // Update DOM
            document.getElementById('totalClasses').textContent = data.totalClasses || 0;
            document.getElementById('classesToday').textContent = data.classesToday || 0;
            document.getElementById('todayClassesCount').textContent = data.classesToday || 0;
            document.getElementById('nextClassTime').textContent = data.nextClassTime || '--:--';
            document.getElementById('avgAttendance').textContent = 'N/A';
            const todayClassesDiv = document.getElementById('todayClasses');
            todayClassesDiv.innerHTML = data.todayClasses?.length ? data.todayClasses.map(cls => `
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="font-medium">${cls.name}</p>
                    <p class="text-sm text-gray-600">Students: ${cls.studentCount || 0}</p>
                    <button onclick="takeAttendance('${cls._id}')" class="text-indigo-600 hover:text-indigo-800">Take Attendance</button>
                </div>
            `).join('') : '<p class="text-gray-600">No classes today.</p>';
        } catch (error) {
            console.error('Error fetching dashboard:', error);
            document.getElementById('todayClasses').innerHTML = '<p class="text-red-600">Failed to load dashboard data.</p>';
        }
    }

    async function fetchClasses() {
        try {
            const response = await fetch('api/teacher/classes', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });
            console.log('HTTP Status (classes):', response.status, response.statusText);
            const text = await response.text();
            console.log('Raw response from /api/teacher/classes:', text);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} ${response.statusText}`);
            }
            const data = JSON.parse(text);
            console.log('Classes data:', data);

            // Update DOM
            const classesList = document.getElementById('classesList');
            classesList.innerHTML = data.length ? data.map(cls => `
                <div class="p-6 bg-white rounded-xl shadow-md">
                    <h3 class="text-lg font-semibold">${cls.name}</h3>
                    <p class="text-sm text-gray-600">Students: ${cls.studentCount || 0}</p>
                    <button onclick="takeAttendance('${cls._id}')" class="mt-2 text-indigo-600 hover:text-indigo-800">Take Attendance</button>
                </div>
            `).join('') : '<p class="text-gray-600">No classes assigned.</p>';

            // Update class select for attendance
            const classSelect = document.getElementById('classSelect');
            classSelect.innerHTML = '<option value="">Select a class</option>' + data.map(cls => `
                <option value="${cls._id}">${cls.name}</option>
            `).join('');
        } catch (error) {
            console.error('Error fetching classes:', error);
            document.getElementById('classesList').innerHTML = '<p class="text-red-600">Failed to load classes.</p>';
        }
    }

    async function fetchSchedule() {
        try {
            const response = await fetch('api/teacher/schedule', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });
            console.log('HTTP Status (schedule):', response.status, response.statusText);
            const text = await response.text();
            console.log('Raw response from /api/teacher/schedule:', text);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} ${response.statusText}`);
            }
            const data = JSON.parse(text);
            console.log('Schedule data:', data);

            // Update DOM
            const scheduleList = document.getElementById('scheduleList');
            scheduleList.innerHTML = data.length ? data.map(entry => `
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="font-medium">Class ID: ${entry.class_id}</p>
                    <p class="text-sm text-gray-600">Day: ${entry.day}</p>
                    <p class="text-sm text-gray-600">Time: ${entry.time}</p>
                </div>
            `).join('') : '<p class="text-gray-600">No schedule available.</p>';
        } catch (error) {
            console.error('Error fetching schedule:', error);
            document.getElementById('scheduleList').innerHTML = '<p class="text-red-600">Failed to load schedule.</p>';
        }
    }

    async function fetchStudents(classId) {
        try {
                const response = await fetch(`api/teacher/attendance?classId=${classId}`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });
            console.log('HTTP Status (students):', response.status, response.statusText);
            const text = await response.text();
            console.log('Raw response from /api/teacher/students:', text);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} ${response.statusText}`);
            }
            const data = JSON.parse(text);
            console.log('Students data:', data);
            return data;
        } catch (error) {
            console.error('Error fetching students:', error);
            throw error;
        }
    }

    async function fetchAttendanceSummary(classId) {
        try {
                const response = await fetch(`api/teacher/attendance?classId=${classId}&action=summary`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });
            console.log('HTTP Status (attendance summary):', response.status, response.statusText);
            const text = await response.text();
            console.log('Raw response from /api/teacher/attendance/summary:', text);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} ${response.statusText}`);
            }
            const data = JSON.parse(text);
            console.log('Attendance summary data:', data);
            return data;
        } catch (error) {
            console.error('Error fetching    summary:', error);
            return { present: 0, absent: 0, late: 0 };
        }
    }

    document.getElementById('classSelect').addEventListener('change', async function() {
        const classId = this.value;
        const form = document.getElementById('attendanceForm');
        const submitBtn = document.getElementById('saveAttendanceBtn');
        const summaryDiv = document.getElementById('attendanceSummary');

        if (classId) {
            try {
                // Fetch students
                const students = await fetchStudents(classId);
                // Fetch attendance summary
                const summary = await fetchAttendanceSummary(classId);

                // Update summary
                document.getElementById('presentCount').textContent = summary.present || 0;
                document.getElementById('absentCount').textContent = summary.absent || 0;
                document.getElementById('lateCount').textContent = summary.late || 0;
                summaryDiv.classList.remove('hidden');

                // Render student list
                form.innerHTML = students.length ? `
                    <div class="space-y-4">
                        ${students.map(student => `
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <p class="text-gray-800">${student.name}</p>
                                <select name="status-${student._id}" class="p-2 border rounded">
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                </select>
                            </div>
                        `).join('')}
                    </div>
                ` : '<p class="text-gray-600">No students in this class.</p>';

                submitBtn.disabled = !students.length;
            } catch (error) {
                form.innerHTML = `<p class="text-red-600">Failed to load students: ${error.message}</p>`;
                summaryDiv.classList.add('hidden');
                submitBtn.disabled = true;
            }
        } else {
            form.innerHTML = '<p class="text-gray-600">Select a class to take attendance.</p>';
            summaryDiv.classList.add('hidden');
            submitBtn.disabled = true;
        }
    });

    function takeAttendance(classId) {
        window.location.href = `#attendance`;
        const classSelect = document.getElementById('classSelect');
        classSelect.value = classId;
        classSelect.dispatchEvent(new Event('change'));
        showSection(null, 'attendance');
    }

    async function saveAttendance() {
        const classId = document.getElementById('classSelect').value;
        const students = await fetchStudents(classId); // Re-fetch to get student IDs
        const attendanceRecords = students.map(student => ({
            studentId: student._id,
            status: document.querySelector(`[name="status-${student._id}"]`).value
        }));

        try {
            const response = await fetch('api/teacher/attendance', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ classId, date: new Date().toISOString().split('T')[0], attendanceRecords })
            });
            console.log('Attendance submission response:', response.status, response.statusText);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status} ${response.statusText}`);
            const result = await response.json();
            alert(result.message || 'Attendance saved successfully!');
            
            // Refresh summary
            const summary = await fetchAttendanceSummary(classId);
            document.getElementById('presentCount').textContent = summary.present || 0;
            document.getElementById('absentCount').textContent = summary.absent || 0;
            document.getElementById('lateCount').textContent = summary.late || 0;
        } catch (error) {
            console.error('Error saving attendance:', error);
            alert('Failed to save attendance: ' + error.message);
        }
    }

    async function fetchReports() {
        const reportType = document.getElementById('reportType');
        const reportsOutput = document.getElementById('reportsOutput');
        reportType.addEventListener('change', async function() {
            const type = this.value;
            if (!type) {
                reportsOutput.innerHTML = '<p class="text-gray-600">Select a report type to view data.</p>';
                return;
            }
            try {
                const response = await fetch(`api/teacher/reports?type=${type}`, { headers: { 'Accept': 'application/json' } });
                console.log('HTTP Status (reports):', response.status, response.statusText);
                const rawResponse = await response.text();
                console.log('Raw response from /api/teacher/reports:', rawResponse);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status} ${response.statusText}`);
                const data = JSON.parse(rawResponse);
                reportsOutput.innerHTML = data.length ? data.map(report => `
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p>Class: ${report.className || 'Unnamed'}</p>
                        <p>Date: ${report.date ? new Date(report.date).toLocaleDateString() : 'N/A'}</p>
                        <p>Attendance: ${report.attendance || 0}%</p>
                    </div>
                `).join('') : '<p class="text-gray-600">No data for this report type.</p>';
            } catch (error) {
                console.error(`Error fetching ${type} report:`, error);
                reportsOutput.innerHTML = `<p class="text-red-600">Failed to load report: ${error.message}</p>`;
            }
        });
    }

    document.getElementById('profileForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await fetch('api/teacher/profile', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: formData.get('name'),
                    email: formData.get('email')
                })
            });
            console.log('Profile update response:', response.status, response.statusText);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status} ${response.statusText}`);
            const result = await response.json();
            alert(result.message || 'Profile updated successfully!');
            window.location.reload();
        } catch (error) {
            console.error('Error updating profile:', error);
            alert('Failed to update profile: ' + error.message);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        fetchDashboard();
        fetchClasses();
        fetchReports();
        fetchSchedule();
    });
    </script>
</body>
</html> 