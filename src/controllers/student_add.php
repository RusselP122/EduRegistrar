<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /EduRegistrar/public/login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getMongoDB();
        $collection = $db->students;

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
        $year = $_POST['year'] ?? '';
        $section = $_POST['section'] ?? '';

        // Validate inputs
        if (empty($name) || empty($email) || empty($password) || empty($year) || empty($section)) {
            throw new Exception('All fields are required');
        }
        if (!in_array($year, ['1st Year', '2nd Year', '3rd Year', '4th Year'])) {
            throw new Exception('Invalid year');
        }
        if (!in_array($section, ['D', 'SMP-D', 'WMAD-D'])) {
            throw new Exception('Invalid section');
        }

        // Check for duplicate email
        if ($collection->findOne(['email' => $email])) {
            throw new Exception('Email already exists');
        }

        // Insert student
        $result = $collection->insertOne([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'year' => $year,
            'section' => $section,
            'role' => 'student'
        ]);

        if ($result->getInsertedCount() > 0) {
            header('Location: /EduRegistrar/public/dashboard');
            exit;
        } else {
            throw new Exception('Failed to add student');
        }
    } catch (Exception $e) {
        error_log("Error adding student: " . $e->getMessage());
        // Display error (or redirect with error message)
        echo "<p class='text-red-600'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}