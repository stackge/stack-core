<?php require 'config/db.php'; ?>
<?php include 'includes/header.php'; ?>

<h2>მომხმარებლების სია</h2>
<a href="create.php">+ დაამატე ახალი</a>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>სახელი</th>
        <th>ელ. ფოსტა</th>
        <th>მოქმედება</th>
    </tr>
    <?php
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    while ($row = $stmt->fetch()) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['email']}</td>
            <td>
                <a href='update.php?id={$row['id']}'>რედ.</a> | 
                <a href='delete.php?id={$row['id']}' onclick='return confirm(\"დარწმუნებული ხარ?\")'>წაშლა</a>
            </td>
        </tr>";
    }
    ?>
</table>

<?php include 'includes/footer.php'; ?>
