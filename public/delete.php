<?php
session_start();
require 'config/db.php';

$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? '';
$action = $_GET['action'] ?? '';

if (!$table) {
    $_SESSION['message'] = 'ცხრილის სახელი აუცილებელია';
    $_SESSION['message_type'] = 'danger';
    header("Location: tables.php");
    exit;
}

try {
    if ($action === 'drop_table') {
        // Drop entire table
        $sql = "DROP TABLE `$table`";
        $pdo->exec($sql);
        
        $_SESSION['message'] = "ცხრილი '$table' წარმატებით წაიშალა";
        $_SESSION['message_type'] = 'success';
        header("Location: tables.php");
        exit;
        
    } else {
        // Delete single record
        if (!$id) {
            throw new Exception('ჩანაწერის ID აუცილებელია');
        }
        
        // Get table structure to find primary key
        $columnsStmt = $pdo->prepare("DESCRIBE `$table`");
        $columnsStmt->execute();
        $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $primaryKey = '';
        foreach ($columns as $col) {
            if ($col['Key'] === 'PRI') {
                $primaryKey = $col['Field'];
                break;
            }
        }
        
        if (!$primaryKey) {
            throw new Exception('Primary key არ მოიძებნა');
        }
        
        $sql = "DELETE FROM `$table` WHERE `$primaryKey` = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = 'ჩანაწერი წარმატებით წაიშალა';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'ჩანაწერი არ მოიძებნა';
            $_SESSION['message_type'] = 'warning';
        }
        
        header("Location: view_table.php?table=" . urlencode($table));
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['message'] = 'შეცდომა წაშლისას: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
} catch (Exception $e) {
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

if ($action === 'drop_table') {
    header("Location: tables.php");
} else {
    header("Location: view_table.php?table=" . urlencode($table));
}
exit;