<?php
/**
 * STACK Core - Actions Handler
 * Dynamic CRUD Management System
 * 
 * @author Skryper
 * @website https://stack.ge/
 * @version 1.0.0
 */

require_once '../config/db.php';
require_once '../core/DatabaseManager.php';

// Initialize Database Manager
$dbManager = new DatabaseManager($pdo);

// Get action and parameters
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$table = $_GET['table'] ?? $_POST['table'] ?? '';

// Validate CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_token'] ?? '';
    if (!validateCSRFToken($token)) {
        $_SESSION['message'] = 'უსაფრთხოების ტოკენი არასწორია';
        $_SESSION['message_type'] = 'danger';
        header('Location: /public/index.php');
        exit;
    }
}

switch ($action) {
    case 'insert_record':
        handleInsertRecord();
        break;
        
    case 'update_record':
        handleUpdateRecord();
        break;
        
    case 'delete_record':
        handleDeleteRecord();
        break;
        
    case 'drop_table':
        handleDropTable();
        break;
        
    case 'create_table':
        handleCreateTable();
        break;
        
    default:
        $_SESSION['message'] = 'უცნობი მოქმედება';
        $_SESSION['message_type'] = 'danger';
        header('Location: /public/index.php');
        exit;
}

/**
 * Handle record insertion
 */
function handleInsertRecord() {
    global $dbManager, $table;
    
    $fields = $_POST['fields'] ?? [];
    
    if (empty($table) || empty($fields)) {
        $_SESSION['message'] = 'არასწორი მონაცემები';
        $_SESSION['message_type'] = 'danger';
        redirectToTable($table);
        return;
    }
    
    $result = $dbManager->insertRecord($table, $fields);
    
    $_SESSION['message'] = $result['message'];
    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
    
    redirectToTable($table);
}

/**
 * Handle record update
 */
function handleUpdateRecord() {
    global $dbManager, $table;
    
    $id = $_POST['id'] ?? '';
    $fields = $_POST['fields'] ?? [];
    
    if (empty($table) || empty($id) || empty($fields)) {
        $_SESSION['message'] = 'არასწორი მონაცემები';
        $_SESSION['message_type'] = 'danger';
        redirectToTable($table);
        return;
    }
    
    $result = $dbManager->updateRecord($table, $id, $fields);
    
    $_SESSION['message'] = $result['message'];
    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
    
    redirectToTable($table);
}

/**
 * Handle record deletion
 */
function handleDeleteRecord() {
    global $dbManager, $table;
    
    $id = $_GET['id'] ?? '';
    
    if (empty($table) || empty($id)) {
        $_SESSION['message'] = 'არასწორი მონაცემები';
        $_SESSION['message_type'] = 'danger';
        redirectToTable($table);
        return;
    }
    
    $result = $dbManager->deleteRecord($table, $id);
    
    $_SESSION['message'] = $result['message'];
    $_SESSION['message_type'] = $result['success'] ? 'success' : 'warning';
    
    redirectToTable($table);
}

/**
 * Handle table deletion
 */
function handleDropTable() {
    global $dbManager, $table;
    
    if (empty($table)) {
        $_SESSION['message'] = 'ცხრილის სახელი აუცილებელია';
        $_SESSION['message_type'] = 'danger';
        header('Location: /public/tables.php');
        exit;
    }
    
    $result = $dbManager->dropTable($table);
    
    $_SESSION['message'] = $result['message'];
    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
    
    header('Location: /public/tables.php');
    exit;
}

/**
 * Handle table creation
 */
function handleCreateTable() {
    global $dbManager;
    
    $tableName = $_POST['table_name'] ?? '';
    $columns = $_POST['columns'] ?? [];
    
    if (empty($tableName) || empty($columns)) {
        $_SESSION['message'] = 'ცხრილის სახელი და სვეტები აუცილებელია';
        $_SESSION['message_type'] = 'danger';
        header('Location: /public/create.php');
        exit;
    }
    
    $result = $dbManager->createTable($tableName, $columns);
    
    $_SESSION['message'] = $result['message'];
    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
    
    if ($result['success']) {
        header('Location: /views/view_table.php?table=' . urlencode($tableName));
    } else {
        header('Location: /public/create.php');
    }
    exit;
}

/**
 * Redirect to table view
 */
function redirectToTable($table) {
    if ($table) {
        header('Location: /views/view_table.php?table=' . urlencode($table));
    } else {
        header('Location: /public/tables.php');
    }
    exit;
}
?>
