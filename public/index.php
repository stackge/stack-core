<?php
/**
 * STACK Core - Main Dashboard
 * Dynamic CRUD Management System
 * 
 * @author Skryper
 * @website https://stack.ge/
 * @version 1.0.0
 */

require_once '../config/db.php';
require_once '../core/DatabaseManager.php';
require_once '../core/UIHelper.php';

// Initialize Database Manager
$dbManager = new DatabaseManager($pdo);

// Get system statistics
$tables = $dbManager->getTables();
$totalTables = count($tables);
$totalRecords = 0;
$tableStats = [];

foreach ($tables as $table) {
    $info = $dbManager->getTableInfo($table);
    $totalRecords += $info['record_count'];
    $tableStats[] = $info;
}

// Sort tables by record count
usort($tableStats, function($a, $b) {
    return $b['record_count'] - $a['record_count'];
});

echo UIHelper::getHeader('მთავარი დაშბორდი');
echo UIHelper::getNavbar('index');
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 mb-3">
            <i class="fas fa-database me-3"></i><?= getAppConfig('name') ?>
        </h1>
        <p class="lead mb-4"><?= getAppConfig('description') ?></p>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-check-circle fs-1 me-3 text-success"></i>
                            <div>
                                <h4 class="mb-0">კავშირი</h4>
                                <small><?= $_SESSION['db_connected'] ? 'წარმატებული' : 'წარუმატებელი' ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-table fs-1 me-3 text-warning"></i>
                            <div>
                                <h4 class="mb-0"><?= number_format($totalTables) ?></h4>
                                <small>ცხრილები</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-chart-bar fs-1 me-3 text-info"></i>
                            <div>
                                <h4 class="mb-0"><?= number_format($totalRecords) ?></h4>
                                <small>ჩანაწერები</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <!-- Quick Actions -->
    <div class="row mb-5">
        <div class="col-md-4 mb-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-plus-circle fa-3x text-success mb-3"></i>
                    <h5 class="card-title">ახალი ცხრილი</h5>
                    <p class="card-text text-muted">შექმენით ახალი ცხრილი დინამიური კონფიგურაციით</p>
                    <a href="create.php" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> შექმნა
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-table fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">ცხრილების მართვა</h5>
                    <p class="card-text text-muted">იხილეთ და მართეთ არსებული ცხრილები</p>
                    <a href="tables.php" class="btn btn-stack">
                        <i class="fas fa-table me-1"></i> მართვა
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="fas fa-database fa-3x text-info mb-3"></i>
                    <h5 class="card-title">სისტემის ინფო</h5>
                    <p class="card-text text-muted">იხილეთ მონაცემთა ბაზის სტატისტიკა</p>
                    <button class="btn btn-info" onclick="showSystemInfo()">
                        <i class="fas fa-info-circle me-1"></i> ინფო
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Statistics -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>მონაცემთა ბაზის სტატისტიკა
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="stats-card card">
                                <div class="card-body">
                                    <i class="fas fa-table fa-2x mb-2"></i>
                                    <h3><?= number_format($totalTables) ?></h3>
                                    <p class="mb-0">სულ ცხრილები</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                    <h3><?= number_format($totalRecords) ?></h3>
                                    <p class="mb-0">სულ ჩანაწერები</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <i class="fas fa-server fa-2x mb-2"></i>
                                    <h3><?= $_SESSION['db_info']['database'] ?? 'N/A' ?></h3>
                                    <p class="mb-0">მონაცემთა ბაზა</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <h3><?= date('H:i') ?></h3>
                                    <p class="mb-0">მიმდინარე დრო</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tables -->
    <?php if (!empty($tableStats)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>ცხრილების მიმოხილვა
                    </h5>
                    <a href="tables.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i> ყველაფრის ნახვა
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach (array_slice($tableStats, 0, 6) as $table): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card table-card h-100" onclick="viewTable('<?= htmlspecialchars($table['name']) ?>')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-table text-primary me-1"></i>
                                            <?= htmlspecialchars($table['name']) ?>
                                        </h6>
                                        <?php if ($table['primary_key']): ?>
                                        <small class="text-success">
                                            <i class="fas fa-key" title="Primary Key: <?= htmlspecialchars($table['primary_key']) ?>"></i>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="text-primary">
                                                <strong><?= number_format($table['record_count']) ?></strong>
                                                <br><small class="text-muted">ჩანაწერები</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-info">
                                                <strong><?= $table['column_count'] ?></strong>
                                                <br><small class="text-muted">სვეტები</small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($table['auto_increment']): ?>
                                    <div class="mt-2">
                                        <small class="text-warning">
                                            <i class="fas fa-plus-square me-1"></i>Auto: <?= htmlspecialchars($table['auto_increment']) ?>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-eye me-1"></i>ნახვა
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
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Empty State -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-table fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">ცხრილები არ მოიძებნა</h4>
                    <p class="text-muted mb-4">მონაცემთა ბაზაში ცხრილები არ არსებობს. შექმენით თქვენი პირველი ცხრილი.</p>
                    <a href="create.php" class="btn btn-stack btn-lg">
                        <i class="fas fa-plus me-2"></i> პირველი ცხრილის შექმნა
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- CRUD Operations Info -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>CRUD ოპერაციები
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="text-success mb-3">
                                <i class="fas fa-plus-circle fa-3x"></i>
                            </div>
                            <h6>Create (შექმნა)</h6>
                            <p class="text-muted small">ახალი ჩანაწერების დამატება ცხრილებში</p>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="text-primary mb-3">
                                <i class="fas fa-eye fa-3x"></i>
                            </div>
                            <h6>Read (წაკითხვა)</h6>
                            <p class="text-muted small">მონაცემების ნახვა, ძიება და ფილტრაცია</p>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="text-warning mb-3">
                                <i class="fas fa-edit fa-3x"></i>
                            </div>
                            <h6>Update (განახლება)</h6>
                            <p class="text-muted small">არსებული ჩანაწერების რედაქტირება</p>
                        </div>
                        <div class="col-md-3 text-center mb-3">
                            <div class="text-danger mb-3">
                                <i class="fas fa-trash fa-3x"></i>
                            </div>
                            <h6>Delete (წაშლა)</h6>
                            <p class="text-muted small">ჩანაწერების უსაფრთხო წაშლა</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewTable(tableName) {
    window.location.href = `view_table.php?table=${encodeURIComponent(tableName)}`;
}

// Auto-refresh statistics every 30 seconds
setInterval(function() {
    // Optional: implement AJAX refresh for statistics
    console.log('Statistics refresh check');
}, 30000);
</script>

<?php echo UIHelper::getFooter(); ?>