<?php
session_start();

// Clear session data
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Clear remember me cookie if set
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/EduRegistrar/public/');
}

// Redirect to login page
header('Location: /EduRegistrar/public/');
exit;
?>