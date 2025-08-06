<?php
/**
 * STACK Core - Tables Management
 * Dynamic CRUD Management System
 * 
 * @author Skryper
 * @website https://stack.ge/
 * @version 1.0.0
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once '../config/db.php';
    require_once '../core/DatabaseManager.php';
    require_once '../core/UIHelper.php';
} catch (Exception $e) {
    die("Error loading dependencies: " . $e->getMessage());
}

// Get messages from session
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

// Initialize Database Manager
try {
    $dbManager = new DatabaseManager($pdo);
    
    // Get all tables with detailed info
    $tables = $dbManager->getTables();
    $tableDetails = [];
    
    foreach ($tables as $tableName) {
        $tableDetails[] = $dbManager->getTableInfo($tableName);
    }
    
} catch (Exception $e) {
    $_SESSION['message'] = "ერორი ცხრილების ჩატვირთვისას: " . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    $tableDetails = [];
}

// Sort tables by name or record count based on user preference
$sortBy = $_GET['sort'] ?? 'name';
$sortDir = $_GET['dir'] ?? 'asc';

usort($tableDetails, function($a, $b) use ($sortBy, $sortDir) {
    $comparison = 0;
    
    switch ($sortBy) {
        case 'records':
            $comparison = $a['record_count'] - $b['record_count'];
            break;
        case 'columns':
            $comparison = $a['column_count'] - $b['column_count'];
            break;
        case 'name':
        default:
            $comparison = strcmp($a['name'], $b['name']);
            break;
    }
    
    return $sortDir === 'desc' ? -$comparison : $comparison;
});

echo UIHelper::getHeader('ცხრილების მართვა');
echo UIHelper::getNavbar('tables');
?>

<div class="container my-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="fas fa-table text-primary me-2"></i>ცხრილების მართვა
            </h2>
            <p class="text-muted mb-0">მართეთ თქვენი მონაცემთა ბაზის ცხრილები</p>
        </div>
        <div>
            <button class="btn btn-info" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i> განახლება
            </button>
            <a href="create.php" class="btn btn-stack">
                <i class="fas fa-plus me-1"></i> ახალი ცხრილი
            </a>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <?= UIHelper::showAlert($message, $messageType) ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card text-center">
                <div class="card-body">
                    <i class="fas fa-table fa-2x mb-2"></i>
                    <h3><?= count($tableDetails) ?></h3>
                    <p class="mb-0">სულ ცხრილები</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white text-center">
                <div class="card-body">
                    <i class="fas fa-chart-bar fa-2x mb-2"></i>
                    <h3><?= number_format(array_sum(array_column($tableDetails, 'record_count'))) ?></h3>
                    <p class="mb-0">სულ ჩანაწერები</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white text-center">
                <div class="card-body">
                    <i class="fas fa-columns fa-2x mb-2"></i>
                    <h3><?= number_format(array_sum(array_column($tableDetails, 'column_count'))) ?></h3>
                    <p class="mb-0">სულ სვეტები</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark text-center">
                <div class="card-body">
                    <i class="fas fa-database fa-2x mb-2"></i>
                    <h3><?= $_SESSION['db_info']['database'] ?? 'N/A' ?></h3>
                    <p class="mb-0">მონაცემთა ბაზა</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sorting Controls -->
    <?php if (!empty($tableDetails)): ?>
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-0">
                        <i class="fas fa-sort me-1"></i>დალაგება
                    </h6>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="sortBy" onchange="updateSort()">
                            <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>სახელი</option>
                            <option value="records" <?= $sortBy === 'records' ? 'selected' : '' ?>>ჩანაწერები</option>
                            <option value="columns" <?= $sortBy === 'columns' ? 'selected' : '' ?>>სვეტები</option>
                        </select>
                        <select class="form-select form-select-sm" id="sortDir" onchange="updateSort()">
                            <option value="asc" <?= $sortDir === 'asc' ? 'selected' : '' ?>>ზრდადი</option>
                            <option value="desc" <?= $sortDir === 'desc' ? 'selected' : '' ?>>კლებადი</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tables Grid -->
    <?php if (empty($tableDetails)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-table fa-4x text-muted mb-4"></i>
            <h4 class="text-muted">ცხრილები არ მოიძებნა</h4>
            <p class="text-muted mb-4">მონაცემთა ბაზაში ცხრილები არ არსებობს. შექმენით თქვენი პირველი ცხრილი.</p>
            <a href="create.php" class="btn btn-stack btn-lg">
                <i class="fas fa-plus me-2"></i> ცხრილის შექმნა
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($tableDetails as $table): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card table-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-table text-primary me-1"></i>
                        <span class="table-name" title="<?= htmlspecialchars($table['name']) ?>">
                            <?= UIHelper::truncateText($table['name'], 20) ?>
                        </span>
                    </h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" type="button" 
                                data-bs-toggle="dropdown" onclick="event.stopPropagation()">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="../views/view_table.php?table=<?= urlencode($table['name']) ?>">
                                    <i class="fas fa-eye me-1"></i> ნახვა და რედაქტირება
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showTableStructure('<?= htmlspecialchars($table['name']) ?>')">
                                    <i class="fas fa-info-circle me-1"></i> სტრუქტურა
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="exportTable('<?= htmlspecialchars($table['name']) ?>')">
                                    <i class="fas fa-download me-1"></i> ექსპორტი
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" 
                                   onclick="deleteTable('<?= htmlspecialchars($table['name']) ?>')">
                                    <i class="fas fa-trash me-1"></i> წაშლა
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body" onclick="viewTable('<?= htmlspecialchars($table['name']) ?>')">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="text-primary">
                                <i class="fas fa-chart-bar"></i>
                                <h5 class="mt-1 mb-0"><?= number_format($table['record_count']) ?></h5>
                                <small class="text-muted">ჩანაწერები</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-info">
                                <i class="fas fa-columns"></i>
                                <h5 class="mt-1 mb-0"><?= $table['column_count'] ?></h5>
                                <small class="text-muted">სვეტები</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Table Properties -->
                    <div class="mb-2">
                        <?php if ($table['primary_key']): ?>
                        <span class="badge bg-success me-1" title="Primary Key">
                            <i class="fas fa-key me-1"></i><?= htmlspecialchars($table['primary_key']) ?>
                        </span>
                        <?php endif; ?>
                        
                        <?php if ($table['auto_increment']): ?>
                        <span class="badge bg-warning text-dark" title="Auto Increment">
                            <i class="fas fa-plus-square me-1"></i><?= htmlspecialchars($table['auto_increment']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Recent Columns Preview -->
                    <div class="mt-3">
                        <small class="text-muted d-block mb-1">სვეტები:</small>
                        <div class="d-flex flex-wrap gap-1">
                            <?php 
                            $displayColumns = array_slice($table['columns'], 0, 3);
                            foreach ($displayColumns as $column): 
                            ?>
                            <span class="badge bg-light text-dark" title="<?= htmlspecialchars($column['Type']) ?>">
                                <?= htmlspecialchars($column['Field']) ?>
                            </span>
                            <?php endforeach; ?>
                            
                            <?php if (count($table['columns']) > 3): ?>
                            <span class="badge bg-secondary">
                                +<?= count($table['columns']) - 3 ?> სხვა
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>ბოლო განახლება: <?= date('H:i') ?>
                        </small>
                        <small class="text-primary">
                            <i class="fas fa-arrow-right"></i>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Table Structure Modal -->
<div class="modal fade" id="structureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>ცხრილის სტრუქტურა
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="structureContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">დახურვა</button>
            </div>
        </div>
    </div>
</div>

<script>
const tableDetails = <?= json_encode($tableDetails) ?>;

function viewTable(tableName) {
    window.location.href = `../views/view_table.php?table=${encodeURIComponent(tableName)}`;
}

function updateSort() {
    const sortBy = document.getElementById('sortBy').value;
    const sortDir = document.getElementById('sortDir').value;
    
    const url = new URL(window.location);
    url.searchParams.set('sort', sortBy);
    url.searchParams.set('dir', sortDir);
    
    window.location.href = url.toString();
}

function showTableStructure(tableName) {
    const table = tableDetails.find(t => t.name === tableName);
    if (!table) return;
    
    let content = `
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ველი</th>
                        <th>ტიპი</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    table.columns.forEach(column => {
        content += `
            <tr>
                <td><code>${column.Field}</code></td>
                <td><span class="badge bg-info">${column.Type}</span></td>
                <td>${column.Null === 'YES' ? '<span class="text-success">YES</span>' : '<span class="text-danger">NO</span>'}</td>
                <td>${column.Key ? '<span class="badge bg-warning">' + column.Key + '</span>' : ''}</td>
                <td>${column.Default || '-'}</td>
                <td>${column.Extra || '-'}</td>
            </tr>
        `;
    });
    
    content += `
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <h6>სტატისტიკა:</h6>
            <ul class="list-unstyled">
                <li><strong>ჩანაწერები:</strong> ${table.record_count.toLocaleString()}</li>
                <li><strong>სვეტები:</strong> ${table.column_count}</li>
                <li><strong>Primary Key:</strong> ${table.primary_key || 'არ არის'}</li>
                <li><strong>Auto Increment:</strong> ${table.auto_increment || 'არ არის'}</li>
            </ul>
        </div>
    `;
    
    document.getElementById('structureContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('structureModal')).show();
}

function exportTable(tableName) {
    // For future implementation
    showSuccess(`ცხრილი "${tableName}"-ის ექსპორტი მალე იქნება ხელმისაწვდომი`);
}

function deleteTable(tableName) {
    confirmAction(
        `ცხრილი "${tableName}" და მისი ყველა მონაცემი წაიშლება. ეს მოქმედება შეუქცევადია!`,
        function() {
            showLoading();
            window.location.href = `../core/actions.php?action=drop_table&table=${encodeURIComponent(tableName)}`;
        }
    );
}

// Auto-refresh every 60 seconds
setInterval(function() {
    // Optional: implement AJAX refresh
    console.log('Tables refresh check');
}, 60000);
</script>

<?php echo UIHelper::getFooter(); ?>
