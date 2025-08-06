<?php
session_start();
require 'config/db.php';

$table = $_POST['table'] ?? '';
$id = $_POST['id'] ?? '';
$fields = $_POST['fields'] ?? [];

if (!$table || !$id || empty($fields)) {
    $_SESSION['message'] = 'არასწორი მონაცემები';
    $_SESSION['message_type'] = 'danger';
    header("Location: view_table.php?table=" . urlencode($table));
    exit;
}

try {
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

    // Build update query
    $setParts = [];
    $params = [];
    
    foreach ($fields as $field => $value) {
        $setParts[] = "`$field` = :$field";
        $params[$field] = $value;
    }
    
    $params['id'] = $id;
    
    $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE `$primaryKey` = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = 'ჩანაწერი წარმატებით განახლდა';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'ცვლილებები არ იყო საჭირო';
        $_SESSION['message_type'] = 'info';
    }
    
} catch (PDOException $e) {
    $_SESSION['message'] = 'შეცდომა ჩანაწერის განახლებისას: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
} catch (Exception $e) {
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

header("Location: view_table.php?table=" . urlencode($table));
exit;