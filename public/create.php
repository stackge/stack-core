<?php
/**
 * STACK Core - Table Creator
 * Dynamic CRUD Management System
 * 
 * @author Skryper
 * @website https://stack.ge/
 * @version 1.0.0
 */

require_once '../config/db.php';
require_once '../core/DatabaseManager.php';
require_once '../core/UIHelper.php';

// Get messages from session
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

echo UIHelper::getHeader('ცხრილის შექმნა');
echo UIHelper::getNavbar('create');
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-stack text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>ახალი ცხრილის შექმნა
                    </h4>
                    <p class="mb-0 mt-2 opacity-75">შექმენით მონაცემთა ბაზის ცხრილი დინამიური კონფიგურაციით</p>
                </div>
                <div class="card-body">
                    <!-- Messages -->
                    <?php if ($message): ?>
                        <?= UIHelper::showAlert($message, $messageType) ?>
                    <?php endif; ?>

                    <form method="POST" action="../core/actions.php" id="createTableForm">
                        <input type="hidden" name="action" value="create_table">
                        <input type="hidden" name="_token" value="<?= generateCSRFToken() ?>">
                        
                        <!-- Table Name -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="table_name" class="form-label">
                                    <i class="fas fa-table me-1"></i>ცხრილის სახელი
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="table_name" name="table_name" 
                                       value="<?= htmlspecialchars($_POST['table_name'] ?? '') ?>" 
                                       placeholder="მაგ: users, products, orders" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    გამოიყენეთ მხოლოდ ლათინური ასოები, ციფრები და ქვედა ხაზი
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-magic me-1"></i>სწრაფი შაბლონები
                                </label>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadTemplate('users')">
                                        <i class="fas fa-users me-1"></i>მომხმარებლები
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="loadTemplate('products')">
                                        <i class="fas fa-box me-1"></i>პროდუქტები
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Columns Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-columns me-2"></i>სვეტების კონფიგურაცია
                                </h5>
                                <button type="button" class="btn btn-success" onclick="addColumn()">
                                    <i class="fas fa-plus me-1"></i>სვეტის დამატება
                                </button>
                            </div>

                            <div id="columns-container">
                                <!-- Default ID column -->
                                <div class="column-row" data-index="0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">
                                            <i class="fas fa-key text-warning me-1"></i>სვეტი #1
                                        </h6>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-column" 
                                                onclick="removeColumn(0)" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">სვეტის სახელი</label>
                                            <input type="text" class="form-control" name="columns[0][name]" 
                                                   placeholder="id" value="id" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">მონაცემის ტიპი</label>
                                            <select class="form-select" name="columns[0][type]" required>
                                                <option value="INT" selected>INT</option>
                                                <option value="VARCHAR">VARCHAR</option>
                                                <option value="TEXT">TEXT</option>
                                                <option value="LONGTEXT">LONGTEXT</option>
                                                <option value="DATE">DATE</option>
                                                <option value="DATETIME">DATETIME</option>
                                                <option value="TIMESTAMP">TIMESTAMP</option>
                                                <option value="DECIMAL">DECIMAL</option>
                                                <option value="FLOAT">FLOAT</option>
                                                <option value="DOUBLE">DOUBLE</option>
                                                <option value="BOOLEAN">BOOLEAN</option>
                                                <option value="TINYINT">TINYINT</option>
                                                <option value="SMALLINT">SMALLINT</option>
                                                <option value="BIGINT">BIGINT</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">სიგრძე/ზუსტობა</label>
                                            <input type="text" class="form-control" name="columns[0][length]" 
                                                   placeholder="11" value="11">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">ნაგულისხმევი მნიშვნელობა</label>
                                            <input type="text" class="form-control" name="columns[0][default]" 
                                                   placeholder="არასავალდებულო">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">თვისებები</label>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="columns[0][null]" id="null_0">
                                                        <label class="form-check-label" for="null_0">NULL</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="columns[0][primary]" id="primary_0" checked>
                                                        <label class="form-check-label" for="primary_0">PRIMARY KEY</label>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="columns[0][auto_increment]" id="auto_0" checked>
                                                        <label class="form-check-label" for="auto_0">AUTO_INCREMENT</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="columns[0][unique]" id="unique_0">
                                                        <label class="form-check-label" for="unique_0">UNIQUE</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>უკან დაბრუნება
                            </a>
                            <div>
                                <button type="button" class="btn btn-info me-2" onclick="previewSQL()">
                                    <i class="fas fa-eye me-1"></i>SQL კოდის გადახედვა
                                </button>
                                <button type="button" class="btn btn-warning me-2" onclick="resetForm()">
                                    <i class="fas fa-undo me-1"></i>გასუფთავება
                                </button>
                                <button type="submit" class="btn btn-stack">
                                    <i class="fas fa-save me-1"></i>ცხრილის შექმნა
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SQL Preview Modal -->
<div class="modal fade" id="sqlPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-code me-2"></i>SQL კოდის გადახედვა
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i>
                    ქვემოთ მოცემულია SQL ოპერატორი, რომელიც შესრულდება ცხრილის შექმნისას
                </div>
                <pre id="sqlPreview" class="bg-dark text-light p-3 rounded" style="font-family: 'Courier New', monospace;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">დახურვა</button>
                <button type="button" class="btn btn-stack" onclick="copyToClipboard()">
                    <i class="fas fa-copy me-1"></i>კოპირება
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-question-circle me-2"></i>დახმარება - მონაცემის ტიპები
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">რიცხვითი ტიპები:</h6>
                        <ul class="small">
                            <li><strong>INT:</strong> მთელი რიცხვები (-2147483648 დან 2147483647-მდე)</li>
                            <li><strong>BIGINT:</strong> დიდი მთელი რიცხვები</li>
                            <li><strong>DECIMAL:</strong> ზუსტი ათობითი რიცხვები</li>
                            <li><strong>FLOAT/DOUBLE:</strong> მცურავი მძიმით რიცხვები</li>
                        </ul>
                        
                        <h6 class="text-success">ტექსტური ტიპები:</h6>
                        <ul class="small">
                            <li><strong>VARCHAR:</strong> ცვლადი სიგრძის ტექსტი (1-65535)</li>
                            <li><strong>TEXT:</strong> გრძელი ტექსტი (65535 სიმბოლოამდე)</li>
                            <li><strong>LONGTEXT:</strong> ძალიან გრძელი ტექსტი</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-warning">თარიღისა და დროის ტიპები:</h6>
                        <ul class="small">
                            <li><strong>DATE:</strong> თარიღი (YYYY-MM-DD)</li>
                            <li><strong>DATETIME:</strong> თარიღი და დრო</li>
                            <li><strong>TIMESTAMP:</strong> Unix timestamp</li>
                        </ul>
                        
                        <h6 class="text-info">სხვა ტიპები:</h6>
                        <ul class="small">
                            <li><strong>BOOLEAN:</strong> ლოგიკური მნიშვნელობა (TRUE/FALSE)</li>
                            <li><strong>TINYINT:</strong> პატარა მთელი რიცხვები (0-255)</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">დახურვა</button>
            </div>
        </div>
    </div>
</div>

<script>
let columnIndex = 1;

// Column templates
const templates = {
    users: [
        {name: 'id', type: 'INT', length: '11', primary: true, auto_increment: true},
        {name: 'username', type: 'VARCHAR', length: '50', null: false},
        {name: 'email', type: 'VARCHAR', length: '100', null: false, unique: true},
        {name: 'password', type: 'VARCHAR', length: '255', null: false},
        {name: 'created_at', type: 'TIMESTAMP', default: 'CURRENT_TIMESTAMP', null: false},
        {name: 'updated_at', type: 'TIMESTAMP', default: 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'}
    ],
    products: [
        {name: 'id', type: 'INT', length: '11', primary: true, auto_increment: true},
        {name: 'name', type: 'VARCHAR', length: '255', null: false},
        {name: 'description', type: 'TEXT'},
        {name: 'price', type: 'DECIMAL', length: '10,2', null: false},
        {name: 'category', type: 'VARCHAR', length: '100'},
        {name: 'in_stock', type: 'BOOLEAN', default: '1'},
        {name: 'created_at', type: 'TIMESTAMP', default: 'CURRENT_TIMESTAMP'}
    ]
};

function addColumn() {
    const container = document.getElementById('columns-container');
    const columnHtml = `
        <div class="column-row" data-index="${columnIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">
                    <i class="fas fa-columns text-primary me-1"></i>სვეტი #${columnIndex + 1}
                </h6>
                <button type="button" class="btn btn-outline-danger btn-sm remove-column" 
                        onclick="removeColumn(${columnIndex})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">სვეტის სახელი</label>
                    <input type="text" class="form-control" name="columns[${columnIndex}][name]" 
                           placeholder="column_name" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">მონაცემის ტიპი</label>
                    <select class="form-select" name="columns[${columnIndex}][type]" required>
                        <option value="VARCHAR" selected>VARCHAR</option>
                        <option value="INT">INT</option>
                        <option value="TEXT">TEXT</option>
                        <option value="LONGTEXT">LONGTEXT</option>
                        <option value="DATE">DATE</option>
                        <option value="DATETIME">DATETIME</option>
                        <option value="TIMESTAMP">TIMESTAMP</option>
                        <option value="DECIMAL">DECIMAL</option>
                        <option value="FLOAT">FLOAT</option>
                        <option value="DOUBLE">DOUBLE</option>
                        <option value="BOOLEAN">BOOLEAN</option>
                        <option value="TINYINT">TINYINT</option>
                        <option value="SMALLINT">SMALLINT</option>
                        <option value="BIGINT">BIGINT</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">სიგრძე/ზუსტობა</label>
                    <input type="text" class="form-control" name="columns[${columnIndex}][length]" 
                           placeholder="255">
                </div>
                <div class="col-md-2">
                    <label class="form-label">ნაგულისხმევი მნიშვნელობა</label>
                    <input type="text" class="form-control" name="columns[${columnIndex}][default]" 
                           placeholder="არასავალდებულო">
                </div>
                <div class="col-md-3">
                    <label class="form-label">თვისებები</label>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="columns[${columnIndex}][null]" id="null_${columnIndex}">
                                <label class="form-check-label" for="null_${columnIndex}">NULL</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="columns[${columnIndex}][primary]" id="primary_${columnIndex}">
                                <label class="form-check-label" for="primary_${columnIndex}">PRIMARY KEY</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="columns[${columnIndex}][auto_increment]" id="auto_${columnIndex}">
                                <label class="form-check-label" for="auto_${columnIndex}">AUTO_INCREMENT</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="columns[${columnIndex}][unique]" id="unique_${columnIndex}">
                                <label class="form-check-label" for="unique_${columnIndex}">UNIQUE</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', columnHtml);
    columnIndex++;
    updateRemoveButtons();
}

function removeColumn(index) {
    const columnRow = document.querySelector(`[data-index="${index}"]`);
    if (columnRow) {
        columnRow.remove();
        updateRemoveButtons();
    }
}

function updateRemoveButtons() {
    const columns = document.querySelectorAll('.column-row');
    columns.forEach((column, index) => {
        const removeBtn = column.querySelector('.remove-column');
        if (columns.length > 1) {
            removeBtn.style.display = 'block';
        } else {
            removeBtn.style.display = 'none';
        }
    });
}

function loadTemplate(templateName) {
    if (!templates[templateName]) return;
    
    // Clear existing columns
    document.getElementById('columns-container').innerHTML = '';
    columnIndex = 0;
    
    // Set table name
    document.getElementById('table_name').value = templateName;
    
    // Add template columns
    templates[templateName].forEach(column => {
        addColumn();
        const currentIndex = columnIndex - 1;
        
        // Fill column data
        document.querySelector(`[name="columns[${currentIndex}][name]"]`).value = column.name;
        document.querySelector(`[name="columns[${currentIndex}][type]"]`).value = column.type;
        
        if (column.length) {
            document.querySelector(`[name="columns[${currentIndex}][length]"]`).value = column.length;
        }
        if (column.default) {
            document.querySelector(`[name="columns[${currentIndex}][default]"]`).value = column.default;
        }
        if (column.null !== undefined) {
            document.querySelector(`[name="columns[${currentIndex}][null]"]`).checked = column.null;
        }
        if (column.primary) {
            document.querySelector(`[name="columns[${currentIndex}][primary]"]`).checked = true;
        }
        if (column.auto_increment) {
            document.querySelector(`[name="columns[${currentIndex}][auto_increment]"]`).checked = true;
        }
        if (column.unique) {
            document.querySelector(`[name="columns[${currentIndex}][unique]"]`).checked = true;
        }
    });
    
    updateRemoveButtons();
    showSuccess('შაბლონი წარმატებით ჩაიტვირთა!');
}

function previewSQL() {
    const tableName = document.getElementById('table_name').value;
    if (!tableName) {
        showError('ცხრილის სახელი აუცილებელია');
        return;
    }

    const columns = document.querySelectorAll('.column-row');
    let sql = `CREATE TABLE \`${tableName}\` (\n`;
    let columnDefs = [];

    columns.forEach(column => {
        const name = column.querySelector('[name*="[name]"]').value;
        if (!name) return;

        const type = column.querySelector('[name*="[type]"]').value;
        const length = column.querySelector('[name*="[length]"]').value;
        const defaultVal = column.querySelector('[name*="[default]"]').value;
        const isNull = column.querySelector('[name*="[null]"]').checked;
        const isPrimary = column.querySelector('[name*="[primary]"]').checked;
        const isAutoIncrement = column.querySelector('[name*="[auto_increment]"]').checked;
        const isUnique = column.querySelector('[name*="[unique]"]').checked;

        let colDef = `  \`${name}\` ${type}`;
        if (length) colDef += `(${length})`;
        colDef += isNull ? ' NULL' : ' NOT NULL';
        if (defaultVal) colDef += ` DEFAULT '${defaultVal}'`;
        if (isPrimary) colDef += ' PRIMARY KEY';
        if (isAutoIncrement) colDef += ' AUTO_INCREMENT';
        if (isUnique && !isPrimary) colDef += ' UNIQUE';

        columnDefs.push(colDef);
    });

    sql += columnDefs.join(',\n') + '\n);';

    document.getElementById('sqlPreview').textContent = sql;
    new bootstrap.Modal(document.getElementById('sqlPreviewModal')).show();
}

function copyToClipboard() {
    const sqlText = document.getElementById('sqlPreview').textContent;
    navigator.clipboard.writeText(sqlText).then(() => {
        showSuccess('SQL კოდი კოპირებულია!');
    });
}

function resetForm() {
    confirmAction('ყველა შეტანილი მონაცემი წაიშლება. გსურთ გაგრძელება?', function() {
        document.getElementById('createTableForm').reset();
        document.getElementById('columns-container').innerHTML = '';
        columnIndex = 0;
        addColumn(); // Add default ID column
        updateRemoveButtons();
        showSuccess('ფორმა გასუფთავებულია');
    });
}

// Initialize
updateRemoveButtons();

// Form validation
document.getElementById('createTableForm').addEventListener('submit', function(e) {
    const tableName = document.getElementById('table_name').value;
    const columns = document.querySelectorAll('.column-row input[name*="[name]"]');
    
    if (!tableName.match(/^[a-zA-Z_][a-zA-Z0-9_]*$/)) {
        e.preventDefault();
        showError('ცხრილის სახელი უნდა შეიცავდეს მხოლოდ ლათინურ ასოებს, ციფრებს და ქვედა ხაზს');
        return;
    }
    
    let hasValidColumn = false;
    columns.forEach(input => {
        if (input.value.trim()) {
            hasValidColumn = true;
        }
    });
    
    if (!hasValidColumn) {
        e.preventDefault();
        showError('მინიმუმ ერთი სვეტი უნდა იყოს განსაზღვრული');
        return;
    }
    
    showLoading();
});
</script>

<?php echo UIHelper::getFooter(); ?>