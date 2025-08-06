<?php
require 'config/db.php';

$table = $_POST['table'] ?? '';
$fields = $_POST['fields'] ?? [];

if (!$table || empty($fields)) {
    $_SESSION['message'] = 'არასწორი მონაცემები';
    $_SESSION['message_type'] = 'danger';
    header("Location: view_table.php?table=" . urlencode($table));
    exit;
}

try {
    // Remove empty fields
    $fields = array_filter($fields, function($value) {
        return $value !== '' && $value !== null;
    });

    if (empty($fields)) {
        throw new Exception('მინიმუმ ერთი ველი უნდა იყოს შევსებული');
    }

    $columns = "`" . implode("`, `", array_keys($fields)) . "`";
    $placeholders = ":" . implode(", :", array_keys($fields));
    $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($fields);
    
    $_SESSION['message'] = 'ჩანაწერი წარმატებით დაემატა';
    $_SESSION['message_type'] = 'success';
    
} catch (PDOException $e) {
    $_SESSION['message'] = 'შეცდომა ჩანაწერის დამატებისას: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
} catch (Exception $e) {
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

header("Location: view_table.php?table=" . urlencode($table));
exit;
