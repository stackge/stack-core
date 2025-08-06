<?php
/**
 * STACK Core - Database Configuration
 * Dynamic CRUD Management System
 * 
 * @author Skryper
 * @website https://stack.ge/
 * @version 1.0.0
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'mydb');
define('DB_USER', 'user');
define('DB_PASS', 'password');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'STACK Core');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'By Skryper');
define('APP_WEBSITE', 'https://stack.ge/');
define('APP_DESCRIPTION', 'Dynamic CRUD Management System');

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('CSRF_TOKEN_NAME', '_token');

// UI Configuration
define('RECORDS_PER_PAGE', 20);
define('MAX_RECORDS_PER_PAGE', 100);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Set session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}

// Database Connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
    ]);
    
    // Store connection in session for debugging
    $_SESSION['db_connected'] = true;
    $_SESSION['db_info'] = [
        'host' => DB_HOST,
        'database' => DB_NAME,
        'charset' => DB_CHARSET
    ];
    
} catch (PDOException $e) {
    $_SESSION['db_connected'] = false;
    $_SESSION['db_error'] = $e->getMessage();
    
    // Log error
    error_log("STACK Core DB Connection failed: " . $e->getMessage());
    
    // Display user-friendly error
    die("
    <!DOCTYPE html>
    <html lang='ka'>
    <head>
        <meta charset='UTF-8'>
        <title>Database Connection Error - " . APP_NAME . "</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-light'>
        <div class='container mt-5'>
            <div class='row justify-content-center'>
                <div class='col-md-6'>
                    <div class='card border-danger'>
                        <div class='card-header bg-danger text-white'>
                            <h4 class='mb-0'><i class='fas fa-exclamation-triangle'></i> Database Connection Error</h4>
                        </div>
                        <div class='card-body'>
                            <p>მონაცემთა ბაზასთან კავშირი ვერ დამყარდა.</p>
                            <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                            <hr>
                            <small class='text-muted'>" . APP_NAME . " " . APP_VERSION . " " . APP_AUTHOR . "</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    ");
}

// Helper Functions
function getAppConfig($key = null) {
    $config = [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'author' => APP_AUTHOR,
        'website' => APP_WEBSITE,
        'description' => APP_DESCRIPTION
    ];
    
    return $key ? ($config[$key] ?? null) : $config;
}

function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function validateCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}
?>
