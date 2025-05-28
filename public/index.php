<?php
session_start();
require_once __DIR__ . '/../src/config/db.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Normalize URI to remove /EduRegistrar/public
$basePath = '/EduRegistrar/public';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath)) ?: '/';
}
$uri = trim($uri, '/');
error_log("Normalized URI: $uri"); // Log to C:\xampp\apache\logs\error.log

if ($uri === '' || ($uri === 'login' && $_SERVER['REQUEST_METHOD'] === 'GET')) {
    if (file_exists(__DIR__ . '/../views/login.php')) {
        require __DIR__ . '/../views/login.php';
    } else {
        error_log("login.php not found at " . __DIR__ . '/../views/login.php');
        http_response_code(404);
        echo "Page not found";
    }
} elseif ($uri === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists(__DIR__ . '/../src/controllers/login.php')) {
        require __DIR__ . '/../src/controllers/login.php';
    } else {
        error_log("login.php controller not found at " . __DIR__ . '/../src/controllers/login.php');
        http_response_code(404);
        echo "Page not found";
    }
} elseif ($uri === 'register') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (file_exists(__DIR__ . '/../src/controllers/register.php')) {
            require __DIR__ . '/../src/controllers/register.php';
        } else {
            error_log("register.php controller not found at " . __DIR__ . '/../src/controllers/register.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        if (file_exists(__DIR__ . '/../views/register.php')) {
            require __DIR__ . '/../views/register.php';
        } else {
            error_log("register.php view not found at " . __DIR__ . '/../views/register.php');
            http_response_code(404);
            echo "Page not found";
        }
    }
} elseif ($uri === 'dashboard') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'teacher'])) {
        if (file_exists(__DIR__ . '/../views/dashboard.php')) {
            require __DIR__ . '/../views/dashboard.php';
        } else {
            error_log("dashboard.php not found at " . __DIR__ . '/../views/dashboard.php');
            http_response_code(404);
            echo "Dashboard not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif ($uri === 'teacher/dashboard') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
        if (file_exists(__DIR__ . '/../views/teacher_dashboard.php')) {
            require __DIR__ . '/../views/teacher_dashboard.php';
        } else {
            error_log("teacher_dashboard.php not found at " . __DIR__ . '/../views/teacher_dashboard.php');
            http_response_code(404);
            echo "Teacher dashboard not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif ($uri === 'teachers') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        if (file_exists(__DIR__ . '/../src/controllers/teachers.php')) {
            require __DIR__ . '/../src/controllers/teachers.php';
        } else {
            error_log("teachers.php not found at " . __DIR__ . '/../src/controllers/teachers.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif ($uri === 'teachers/add') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        if (file_exists(__DIR__ . '/../src/controllers/teacher_add.php')) {
            require __DIR__ . '/../src/controllers/teacher_add.php';
        } else {
            error_log("teacher_add.php not found at " . __DIR__ . '/../src/controllers/teacher_add.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif (preg_match('#^teachers/edit/([a-zA-Z0-9]+)$#', $uri, $matches)) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $_GET['id'] = $matches[1];
        if (file_exists(__DIR__ . '/../src/controllers/teacher_edit.php')) {
            require __DIR__ . '/../src/controllers/teacher_edit.php';
        } else {
            error_log("teacher_edit.php not found at " . __DIR__ . '/../src/controllers/teacher_edit.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif (preg_match('#^teachers/delete/([a-zA-Z0-9]+)$#', $uri, $matches)) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $_GET['id'] = $matches[1];
        if (file_exists(__DIR__ . '/../src/controllers/teacher_delete.php')) {
            require __DIR__ . '/../src/controllers/teacher_delete.php';
        } else {
            error_log("teacher_delete.php not found at " . __DIR__ . '/../src/controllers/teacher_delete.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif ($uri === 'reports') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'teacher'])) {
        if (file_exists(__DIR__ . '/../src/controllers/reports.php')) {
            require __DIR__ . '/../src/controllers/reports.php';
        } else {
            error_log("reports.php not found at " . __DIR__ . '/../src/controllers/reports.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif ($uri === 'mark-attendance') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'teacher'])) {
        if (file_exists(__DIR__ . '/../src/controllers/attendance.php')) {
            require __DIR__ . '/../src/controllers/attendance.php';
        } else {
            error_log("attendance.php not found at " . __DIR__ . '/../src/controllers/attendance.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif ($uri === 'forgot-password') {
    if (file_exists(__DIR__ . '/../views/forgot-password.php')) {
        require __DIR__ . '/../views/forgot-password.php';
    } else {
        error_log("forgot-password.php not found at " . __DIR__ . '/../views/forgot-password.php');
        http_response_code(404);
        echo "Page not found";
    }
} elseif ($uri === 'logout') {
    if (file_exists(__DIR__ . '/../src/controllers/logout.php')) {
        require __DIR__ . '/../src/controllers/logout.php';
    } else {
        error_log("logout.php not found at " . __DIR__ . '/../src/controllers/logout.php');
        http_response_code(404);
        echo "Logout controller not found";
    }
} elseif ($uri === 'api/teacher/dashboard') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
        if (file_exists(__DIR__ . '/../src/controllers/teacher_dashboard_api.php')) {
            require __DIR__ . '/../src/controllers/teacher_dashboard_api.php';
        } else {
            error_log("teacher_dashboard_api.php not found at " . __DIR__ . '/../src/controllers/teacher_dashboard_api.php');
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
    }
} elseif ($uri === 'api/teacher/classes') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
        if (file_exists(__DIR__ . '/../src/controllers/teacher_classes_api.php')) {
            require __DIR__ . '/../src/controllers/teacher_classes_api.php';
        } else {
            error_log("teacher_classes_api.php not found at " . __DIR__ . '/../src/controllers/teacher_classes_api.php');
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
    }
} elseif ($uri === 'api/teacher/schedule') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
        if (file_exists(__DIR__ . '/../src/controllers/teacher_schedule_api.php')) {
            require __DIR__ . '/../src/controllers/teacher_schedule_api.php';
        } else {
            error_log("teacher_schedule_api.php not found at " . __DIR__ . '/../src/controllers/teacher_schedule_api.php');
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
    }
} elseif ($uri === 'api/teacher/attendance') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
        if (file_exists(__DIR__ . '/../src/controllers/teacher_attendance_api.php')) {
            require __DIR__ . '/../src/controllers/teacher_attendance_api.php';
        } else {
            error_log("teacher_attendance_api.php not found at " . __DIR__ . '/../src/controllers/teacher_attendance_api.php');
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
    }
} elseif ($uri === 'api/teachers') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        if (file_exists(__DIR__ . '/../src/controllers/api_teachers.php')) {
            require __DIR__ . '/../src/controllers/api_teachers.php';
        } else {
            error_log("api_teachers.php not found at " . __DIR__ . '/../src/controllers/api_teachers.php');
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
    }
} elseif ($uri === 'api/reports') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'teacher'])) {
        if (file_exists(__DIR__ . '/../src/controllers/api_reports.php')) {
            require __DIR__ . '/../src/controllers/api_reports.php';
        } else {
            error_log("api_reports.php not found at " . __DIR__ . '/../src/controllers/api_reports.php');
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
    }
} elseif ($uri === 'api/teacher/profile') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
        if (file_exists(__DIR__ . '/../src/controllers/teacher_profile_api.php')) {
            require __DIR__ . '/../src/controllers/teacher_profile_api.php';
        } else {
            error_log("teacher_profile_api.php not found at " . __DIR__ . '/../src/controllers/teacher_profile_api.php');
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
    }
} elseif ($uri === 'students') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        if (file_exists(__DIR__ . '/../src/controllers/students.php')) {
            require __DIR__ . '/../src/controllers/students.php';
        } else {
            error_log("students.php not found at " . __DIR__ . '/../src/controllers/students.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif ($uri === 'students/add') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        if (file_exists(__DIR__ . '/../src/controllers/student_add.php')) {
            require __DIR__ . '/../src/controllers/student_add.php';
        } else {
            error_log("student_add.php not found at " . __DIR__ . '/../src/controllers/student_add.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif (preg_match('#^students/edit/([a-zA-Z0-9]+)$#', $uri, $matches)) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $_GET['id'] = $matches[1];
        if (file_exists(__DIR__ . '/../src/controllers/student_edit.php')) {
            require __DIR__ . '/../src/controllers/student_edit.php';
        } else {
            error_log("student_edit.php not found at " . __DIR__ . '/../src/controllers/student_edit.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif (preg_match('#^students/delete/([a-zA-Z0-9]+)$#', $uri, $matches)) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $_GET['id'] = $matches[1];
        if (file_exists(__DIR__ . '/../src/controllers/student_delete.php')) {
            require __DIR__ . '/../src/controllers/student_delete.php';
        } else {
            error_log("student_delete.php not found at " . __DIR__ . '/../src/controllers/student_delete.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif ($uri === 'api/students') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        if (file_exists(__DIR__ . '/../src/controllers/api_students.php')) {
            require __DIR__ . '/../src/controllers/api_students.php';
        } else {
            error_log("api_students.php not found at " . __DIR__ . '/../src/controllers/api_students.php');
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
    }
} elseif ($uri === 'classes') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        if (file_exists(__DIR__ . '/../src/controllers/classes.php')) {
            require __DIR__ . '/../src/controllers/classes.php';
        } else {
            error_log("classes.php not found at " . __DIR__ . '/../src/controllers/classes.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif ($uri === 'classes/add') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        if (file_exists(__DIR__ . '/../src/controllers/classes_add.php')) {
            require __DIR__ . '/../src/controllers/classes_add.php';
        } else {
            error_log("classes_add.php not found at " . __DIR__ . '/../src/controllers/classes_add.php');
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
    }
} elseif (preg_match('#^classes/edit/([a-zA-Z0-9]+)$#', $uri, $matches)) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $_GET['id'] = $matches[1];
        if (file_exists(__DIR__ . '/../src/controllers/classes_edit.php')) {
            require __DIR__ . '/../src/controllers/classes_edit.php';
        } else {
            error_log("classes_edit.php not found at " . __DIR__ . '/../src/controllers/classes_edit.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif (preg_match('#^classes/delete/([a-zA-Z0-9]+)$#', $uri, $matches)) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $_GET['id'] = $matches[1];
        if (file_exists(__DIR__ . '/../src/controllers/classes_delete.php')) {
            require __DIR__ . '/../src/controllers/classes_delete.php';
        } else {
            error_log("classes_delete.php not found at " . __DIR__ . '/../src/controllers/classes_delete.php');
            http_response_code(404);
            echo "Page not found";
        }
    } else {
        header('Location: /EduRegistrar/public/');
        exit;
    }
} elseif ($uri === 'api/classes') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        if (file_exists(__DIR__ . '/../src/controllers/api_classes.php')) {
            require __DIR__ . '/../src/controllers/api_classes.php';
        } else {
            error_log("api_classes.php not found at " . __DIR__ . '/../src/controllers/api_classes.php');
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
        }
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
    }
} elseif ($uri === 'api/teacher/classes') {
    require_once __DIR__ . '/../src/controllers/api_teacher_classes.php';
    exit;
} elseif ($uri === 'api/teacher/schedule') {
    require_once __DIR__ . '/../src/controllers/api_teacher_schedule.php';
    exit;
} elseif ($uri === 'api/teacher/dashboard') {
    require_once __DIR__ . '/../src/controllers/api_teacher_dashboard.php';
    exit;
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>The requested URL was not found on this server.</p>";
}
?>