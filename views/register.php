<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduRegistrar - Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <base href="/EduRegistrar/public/">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo and Header -->
        <div class="text-center mb-8">
            <div class="bg-indigo-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-graduation-cap text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">EduRegistrar</h1>
            <p class="text-gray-600">Smart Attendance Tracking System</p>
        </div>

        <!-- Register Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 text-center mb-2">Create Account</h2>
                <p class="text-gray-600 text-center">Sign up to get started</p>
            </div>

            <!-- Error Message -->
            <?php if (isset($_GET['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <p><?php echo htmlspecialchars($_GET['error']); ?></p>
                </div>
            <?php endif; ?>

          <form action="/EduRegistrar/public/register" method="POST" class="space-y-6">
                
                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2"></i>Full Name
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                        placeholder="Enter your full name"
                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                    >
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                        placeholder="Enter your email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 pr-12"
                            placeholder="Enter your password"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                        >
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Role Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag mr-2"></i>Register as
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="admin" class="sr-only peer" required <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'checked' : ''; ?>>
                            <div class="p-3 text-center border-2 border-gray-300 rounded-lg peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition duration-200">
                                <i class="fas fa-user-cog text-lg mb-1"></i>
                                <p class="text-xs font-medium">Admin</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="teacher" class="sr-only peer" required <?php echo (isset($_POST['role']) && $_POST['role'] === 'teacher') ? 'checked' : ''; ?>>
                            <div class="p-3 text-center border-2 border-gray-300 rounded-lg peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition duration-200">
                                <i class="fas fa-chalkboard-teacher text-lg mb-1"></i>
                                <p class="text-xs font-medium">Teacher</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200"
                >
                    <i class="fas fa-user-plus mr-2"></i>Sign Up
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="/EduRegistrar/public/" class="text-indigo-600 hover:text-indigo-500 font-medium">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500 text-sm">
            <p>© 2025 EduRegistrar. All rights reserved.</p>
            <p class="mt-1">Smart Attendance Tracking for Educational Institutions</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const role = document.querySelector('input[name="role"]:checked');

            if (!name || !email || !password || !role) {
                e.preventDefault();
                alert('Please fill in all required fields and select your role.');
            }
        });
    </script>
</body>
</html>