<?php
session_start();
require '../config/db.php';
require '../core/DatabaseManager.php';
require '../core/UIHelper.php';

$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

$table = $_GET['table'] ?? '';

if (!$table) {
    header('Location: ../public/tables.php');
    exit;
}

// Check if table exists
try {
    $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
} catch (PDOException $e) {
    $message = "ცხრილი '$table' არ არსებობს";
    $messageType = 'danger';
    $table = '';
}

$columns = [];
$records = [];
$totalRecords = 0;

if ($table) {
    // Get table columns
    $columnsStmt = $pdo->prepare("DESCRIBE `$table`");
    $columnsStmt->execute();
    $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get pagination parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(10, min(100, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;

    // Get total count
    $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
    $totalRecords = $countStmt->fetchColumn();

    // Get records with pagination
    $search = $_GET['search'] ?? '';
    $orderBy = $_GET['order'] ?? '';
    $orderDir = $_GET['dir'] ?? 'ASC';

    $query = "SELECT * FROM `$table`";
    $params = [];

    // Add search
    if ($search) {
        $searchConditions = [];
        foreach ($columns as $col) {
            $searchConditions[] = "`{$col['Field']}` LIKE ?";
            $params[] = "%$search%";
        }
        $query .= " WHERE " . implode(' OR ', $searchConditions);
    }

    // Add ordering
    if ($orderBy && in_array($orderBy, array_column($columns, 'Field'))) {
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        $query .= " ORDER BY `$orderBy` $orderDir";
    }

    // Add pagination
    $query .= " LIMIT $limit OFFSET $offset";

    $recordsStmt = $pdo->prepare($query);
    $recordsStmt->execute($params);
    $records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Update total records for search
    if ($search) {
        $countQuery = "SELECT COUNT(*) FROM `$table`";
        if ($search) {
            $searchConditions = [];
            foreach ($columns as $col) {
                $searchConditions[] = "`{$col['Field']}` LIKE ?";
            }
            $countQuery .= " WHERE " . implode(' OR ', $searchConditions);
        }
        $countStmt = $pdo->prepare($countQuery);
        $countStmt->execute($params ? array_slice($params, 0, count($columns)) : []);
        $totalRecords = $countStmt->fetchColumn();
    }

    $totalPages = ceil($totalRecords / $limit);
}
?>

}
?>

<?php 
echo UIHelper::getHeader("ცხრილი: " . htmlspecialchars($table));
echo UIHelper::getNavbar('view_table');
?>

    <div class="container-fluid my-4">
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <div class="text-center">
                                <a href="../public/tables.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i> ცხრილების სია
                </a>
            </div>
        <?php else: ?>
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-table text-primary"></i> 
                    ცხრილი: <span class="text-primary"><?= htmlspecialchars($table) ?></span>
                </h2>
                <div>
                    <a href="../public/tables.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> უკან
                    </a>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                        <i class="fas fa-plus"></i> ჩანაწერის დამატება
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i> მოქმედებები
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportTable()">
                                <i class="fas fa-download"></i> CSV ექსპორტი
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="showTableStructure()">
                                <i class="fas fa-info-circle"></i> ცხრილის სტრუქტურა
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="dropTable()">
                                <i class="fas fa-trash"></i> ცხრილის წაშლა
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-primary"><?= $totalRecords ?></h5>
                            <small class="text-muted">სულ ჩანაწერები</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-info"><?= count($columns) ?></h5>
                            <small class="text-muted">სვეტები</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-success"><?= $page ?>/<?= max(1, $totalPages) ?></h5>
                            <small class="text-muted">გვერდი</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-warning"><?= count($records) ?></h5>
                            <small class="text-muted">ნაჩვენები</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <form method="GET" class="search-form">
                <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">ძიება</label>
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlspecialchars($search) ?>" 
                               placeholder="ძებნა ყველა ველში...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">დალაგება</label>
                        <select class="form-control" name="order">
                            <option value="">-- ავტომატური --</option>
                            <?php foreach ($columns as $col): ?>
                                <option value="<?= htmlspecialchars($col['Field']) ?>" 
                                        <?= $orderBy === $col['Field'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($col['Field']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">მიმართულება</label>
                        <select class="form-control" name="dir">
                            <option value="ASC" <?= $orderDir === 'ASC' ? 'selected' : '' ?>>ზრდადი</option>
                            <option value="DESC" <?= $orderDir === 'DESC' ? 'selected' : '' ?>>კლებადი</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">გვერდზე</label>
                        <select class="form-control" name="limit">
                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                            <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> ძებნა
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Data Table -->
            <?php if (empty($records)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> ჩანაწერები არ მოიძებნა
                    <?php if ($search): ?>
                        - "<strong><?= htmlspecialchars($search) ?></strong>" ძებნისთვის
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <?php foreach ($columns as $col): ?>
                                    <th>
                                        <a href="?table=<?= urlencode($table) ?>&order=<?= urlencode($col['Field']) ?>&dir=<?= $orderBy === $col['Field'] && $orderDir === 'ASC' ? 'DESC' : 'ASC' ?><?= $search ? '&search=' . urlencode($search) : '' ?>&limit=<?= $limit ?>" 
                                           class="text-white text-decoration-none">
                                            <?= htmlspecialchars($col['Field']) ?>
                                            <?php if ($orderBy === $col['Field']): ?>
                                                <i class="fas fa-sort-<?= $orderDir === 'ASC' ? 'up' : 'down' ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                        <br><small class="text-muted"><?= htmlspecialchars($col['Type']) ?></small>
                                    </th>
                                <?php endforeach; ?>
                                <th width="120">მოქმედებები</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $row): ?>
                                <tr>
                                    <?php foreach ($columns as $col): ?>
                                        <td>
                                            <?php 
                                            $value = $row[$col['Field']] ?? '';
                                            if (strlen($value) > 50) {
                                                echo htmlspecialchars(substr($value, 0, 50)) . '...';
                                            } else {
                                                echo htmlspecialchars($value);
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-info" 
                                                onclick="viewRecord(<?= htmlspecialchars(json_encode($row)) ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" 
                                                onclick="editRecord(<?= htmlspecialchars(json_encode($row)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="deleteRecord('<?= htmlspecialchars($row[$columns[0]['Field']] ?? '') ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?table=<?= urlencode($table) ?>&page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>&order=<?= urlencode($orderBy) ?>&dir=<?= urlencode($orderDir) ?>&limit=<?= $limit ?>">
                                        წინა
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php 
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            for ($i = $startPage; $i <= $endPage; $i++): 
                            ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?table=<?= urlencode($table) ?>&page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>&order=<?= urlencode($orderBy) ?>&dir=<?= urlencode($orderDir) ?>&limit=<?= $limit ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?table=<?= urlencode($table) ?>&page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>&order=<?= urlencode($orderBy) ?>&dir=<?= urlencode($orderDir) ?>&limit=<?= $limit ?>">
                                        შემდეგი
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Add Record Modal -->
    <div class="modal fade" id="addRecordModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus"></i> ახალი ჩანაწერის დამატება
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="insert_row.php">
                    <div class="modal-body">
                        <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
                        <?php foreach ($columns as $col): ?>
                            <?php if ($col['Extra'] !== 'auto_increment'): ?>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <?= htmlspecialchars($col['Field']) ?>
                                        <small class="text-muted">(<?= htmlspecialchars($col['Type']) ?>)</small>
                                        <?php if ($col['Null'] === 'NO'): ?>
                                            <span class="text-danger">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php if (strpos($col['Type'], 'text') !== false || strpos($col['Type'], 'longtext') !== false): ?>
                                        <textarea class="form-control" name="fields[<?= htmlspecialchars($col['Field']) ?>]" 
                                                  <?= $col['Null'] === 'NO' ? 'required' : '' ?>></textarea>
                                    <?php else: ?>
                                        <input type="text" class="form-control" 
                                               name="fields[<?= htmlspecialchars($col['Field']) ?>]"
                                               <?= $col['Null'] === 'NO' ? 'required' : '' ?>>
                                    <?php endif; ?>
                                    <?php if (!empty($col['Default'])): ?>
                                        <div class="form-text">ნაგულისხმევი: <?= htmlspecialchars($col['Default']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">გაუქმება</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> შენახვა
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Record Modal -->
    <div class="modal fade" id="viewRecordModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye"></i> ჩანაწერის ნახვა
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewRecordContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">დახურვა</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Record Modal -->
    <div class="modal fade" id="editRecordModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> ჩანაწერის რედაქტირება
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="update.php" id="editForm">
                    <div class="modal-body" id="editRecordContent">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">გაუქმება</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> შენახვა
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const columns = <?= json_encode($columns) ?>;
        const tableName = <?= json_encode($table) ?>;

        function viewRecord(record) {
            let content = '<div class="row">';
            columns.forEach(col => {
                const value = record[col.Field] || '';
                content += `
                    <div class="col-md-6 mb-3">
                        <strong>${col.Field}</strong>
                        <div class="form-control-plaintext border rounded p-2 bg-light">
                            ${value}
                        </div>
                        <small class="text-muted">${col.Type}</small>
                    </div>
                `;
            });
            content += '</div>';
            
            document.getElementById('viewRecordContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('viewRecordModal')).show();
        }

        function editRecord(record) {
            let content = `
                <input type="hidden" name="table" value="${tableName}">
                <input type="hidden" name="id" value="${record[columns[0].Field]}">
            `;
            
            columns.forEach(col => {
                if (col.Extra !== 'auto_increment') {
                    const value = record[col.Field] || '';
                    const required = col.Null === 'NO' ? 'required' : '';
                    
                    content += `
                        <div class="mb-3">
                            <label class="form-label">
                                ${col.Field}
                                <small class="text-muted">(${col.Type})</small>
                                ${col.Null === 'NO' ? '<span class="text-danger">*</span>' : ''}
                            </label>
                    `;
                    
                    if (col.Type.includes('text')) {
                        content += `<textarea class="form-control" name="fields[${col.Field}]" ${required}>${value}</textarea>`;
                    } else {
                        content += `<input type="text" class="form-control" name="fields[${col.Field}]" value="${value}" ${required}>`;
                    }
                    
                    content += '</div>';
                }
            });
            
            document.getElementById('editRecordContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('editRecordModal')).show();
        }

        function deleteRecord(id) {
            if (confirm('დარწმუნებული ხართ, რომ გსურთ ამ ჩანაწერის წაშლა?')) {
                window.location.href = `delete.php?table=${tableName}&id=${id}`;
            }
        }

        function exportTable() {
            window.location.href = `export.php?table=${tableName}`;
        }

        function showTableStructure() {
            alert('ცხრილის სტრუქტურის ნახვა მალე იქნება ხელმისაწვდომი');
        }

        function dropTable() {
            if (confirm(`დარწმუნებული ხართ, რომ გსურთ ცხრილი "${tableName}"-ის წაშლა? ეს მოქმედება შეუქცევადია!`)) {
                if (confirm('ბოლო გაფრთხილება! ყველა მონაცემი წაიშლება!')) {
                    window.location.href = `delete.php?action=drop_table&table=${tableName}`;
                }
            }
        }
    </script>

<?php echo UIHelper::getFooter(); ?>
