<?php
/**
 * STACK Core - Database Manager
 * Dynamic CRUD Management System
 * 
 * @author Skryper
 * @website https://stack.ge/
 * @version 1.0.0
 */

class DatabaseManager {
    private $pdo;
    private $tables = [];
    private $tableStructures = [];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->loadTables();
    }
    
    /**
     * Load all tables from database
     */
    private function loadTables() {
        try {
            $stmt = $this->pdo->query("SHOW TABLES");
            $this->tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error loading tables: " . $e->getMessage());
            $this->tables = [];
        }
    }
    
    /**
     * Get all tables
     */
    public function getTables() {
        return $this->tables;
    }
    
    /**
     * Get table structure
     */
    public function getTableStructure($tableName) {
        if (!isset($this->tableStructures[$tableName])) {
            try {
                $stmt = $this->pdo->prepare("DESCRIBE `$tableName`");
                $stmt->execute();
                $this->tableStructures[$tableName] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error getting table structure for $tableName: " . $e->getMessage());
                return [];
            }
        }
        return $this->tableStructures[$tableName];
    }
    
    /**
     * Get table information (records count, primary key, etc.)
     */
    public function getTableInfo($tableName) {
        $info = [
            'name' => $tableName,
            'record_count' => 0,
            'column_count' => 0,
            'primary_key' => '',
            'auto_increment' => '',
            'columns' => [],
            'indexes' => []
        ];
        
        try {
            // Get record count
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `$tableName`");
            $stmt->execute();
            $info['record_count'] = $stmt->fetchColumn();
            
            // Get structure
            $structure = $this->getTableStructure($tableName);
            $info['column_count'] = count($structure);
            $info['columns'] = $structure;
            
            // Find primary key and auto increment
            foreach ($structure as $column) {
                if ($column['Key'] === 'PRI') {
                    $info['primary_key'] = $column['Field'];
                }
                if ($column['Extra'] === 'auto_increment') {
                    $info['auto_increment'] = $column['Field'];
                }
            }
            
            // Get indexes
            $stmt = $this->pdo->prepare("SHOW INDEX FROM `$tableName`");
            $stmt->execute();
            $info['indexes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting table info for $tableName: " . $e->getMessage());
        }
        
        return $info;
    }
    
    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        return in_array($tableName, $this->tables);
    }
    
    /**
     * Get records with pagination and filtering
     */
    public function getRecords($tableName, $options = []) {
        $page = $options['page'] ?? 1;
        $limit = min($options['limit'] ?? RECORDS_PER_PAGE, MAX_RECORDS_PER_PAGE);
        $search = $options['search'] ?? '';
        $orderBy = $options['order_by'] ?? '';
        $orderDir = strtoupper($options['order_dir'] ?? 'ASC');
        $where = $options['where'] ?? [];
        
        $offset = ($page - 1) * $limit;
        $orderDir = in_array($orderDir, ['ASC', 'DESC']) ? $orderDir : 'ASC';
        
        // Build query
        $query = "SELECT * FROM `$tableName`";
        $countQuery = "SELECT COUNT(*) FROM `$tableName`";
        $params = [];
        
        // Add WHERE conditions
        $whereClauses = [];
        
        // Search conditions
        if ($search) {
            $structure = $this->getTableStructure($tableName);
            $searchClauses = [];
            foreach ($structure as $column) {
                $searchClauses[] = "`{$column['Field']}` LIKE :search";
            }
            if (!empty($searchClauses)) {
                $whereClauses[] = '(' . implode(' OR ', $searchClauses) . ')';
                $params['search'] = "%$search%";
            }
        }
        
        // Custom WHERE conditions
        foreach ($where as $field => $value) {
            $whereClauses[] = "`$field` = :where_$field";
            $params["where_$field"] = $value;
        }
        
        // Apply WHERE clause
        if (!empty($whereClauses)) {
            $whereClause = " WHERE " . implode(' AND ', $whereClauses);
            $query .= $whereClause;
            $countQuery .= $whereClause;
        }
        
        // Add ORDER BY
        if ($orderBy) {
            $structure = $this->getTableStructure($tableName);
            $validColumns = array_column($structure, 'Field');
            if (in_array($orderBy, $validColumns)) {
                $query .= " ORDER BY `$orderBy` $orderDir";
            }
        }
        
        // Add LIMIT
        $query .= " LIMIT $limit OFFSET $offset";
        
        try {
            // Get total count
            $countStmt = $this->pdo->prepare($countQuery);
            $countStmt->execute($params);
            $totalRecords = $countStmt->fetchColumn();
            
            // Get records
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'records' => $records,
                'total' => $totalRecords,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalRecords / $limit),
                'has_more' => $totalRecords > ($page * $limit)
            ];
            
        } catch (PDOException $e) {
            error_log("Error getting records from $tableName: " . $e->getMessage());
            return [
                'records' => [],
                'total' => 0,
                'page' => 1,
                'limit' => $limit,
                'total_pages' => 0,
                'has_more' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Insert record
     */
    public function insertRecord($tableName, $data) {
        try {
            // Remove empty values and auto-increment fields
            $structure = $this->getTableStructure($tableName);
            $autoIncrementField = '';
            foreach ($structure as $column) {
                if ($column['Extra'] === 'auto_increment') {
                    $autoIncrementField = $column['Field'];
                    break;
                }
            }
            
            // Filter data
            $filteredData = [];
            foreach ($data as $field => $value) {
                if ($field !== $autoIncrementField && $value !== '' && $value !== null) {
                    $filteredData[$field] = $value;
                }
            }
            
            if (empty($filteredData)) {
                throw new Exception('მინიმუმ ერთი ველი უნდა იყოს შევსებული');
            }
            
            $columns = "`" . implode("`, `", array_keys($filteredData)) . "`";
            $placeholders = ":" . implode(", :", array_keys($filteredData));
            $sql = "INSERT INTO `$tableName` ($columns) VALUES ($placeholders)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($filteredData);
            
            return [
                'success' => true,
                'insert_id' => $this->pdo->lastInsertId(),
                'message' => 'ჩანაწერი წარმატებით დაემატა'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'შეცდომა ჩანაწერის დამატებისას: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update record
     */
    public function updateRecord($tableName, $id, $data) {
        try {
            $tableInfo = $this->getTableInfo($tableName);
            $primaryKey = $tableInfo['primary_key'];
            
            if (!$primaryKey) {
                throw new Exception('Primary key არ მოიძებნა');
            }
            
            // Build update query
            $setParts = [];
            $params = [];
            
            foreach ($data as $field => $value) {
                if ($field !== $primaryKey) {
                    $setParts[] = "`$field` = :$field";
                    $params[$field] = $value;
                }
            }
            
            if (empty($setParts)) {
                throw new Exception('განახლებადი ველები არ მოიძებნა');
            }
            
            $params['id'] = $id;
            $sql = "UPDATE `$tableName` SET " . implode(', ', $setParts) . " WHERE `$primaryKey` = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'affected_rows' => $stmt->rowCount(),
                'message' => $stmt->rowCount() > 0 ? 'ჩანაწერი წარმატებით განახლდა' : 'ცვლილებები არ იყო საჭირო'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'შეცდომა ჩანაწერის განახლებისას: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete record
     */
    public function deleteRecord($tableName, $id) {
        try {
            $tableInfo = $this->getTableInfo($tableName);
            $primaryKey = $tableInfo['primary_key'];
            
            if (!$primaryKey) {
                throw new Exception('Primary key არ მოიძებნა');
            }
            
            $sql = "DELETE FROM `$tableName` WHERE `$primaryKey` = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return [
                'success' => true,
                'affected_rows' => $stmt->rowCount(),
                'message' => $stmt->rowCount() > 0 ? 'ჩანაწერი წარმატებით წაიშალა' : 'ჩანაწერი არ მოიძებნა'
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'შეცდომა ჩანაწერის წაშლისას: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Drop table
     */
    public function dropTable($tableName) {
        try {
            $sql = "DROP TABLE `$tableName`";
            $this->pdo->exec($sql);
            
            // Reload tables
            $this->loadTables();
            
            return [
                'success' => true,
                'message' => "ცხრილი '$tableName' წარმატებით წაიშალა"
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'შეცდომა ცხრილის წაშლისას: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Create table
     */
    public function createTable($tableName, $columns) {
        try {
            if (empty($tableName) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName)) {
                throw new Exception('ცხრილის სახელი უნდა შეიცავდეს მხოლოდ ლათინურ ასოებს, ციფრებს და ქვედა ხაზს');
            }
            
            $sql = "CREATE TABLE `$tableName` (";
            $columnDefinitions = [];
            
            foreach ($columns as $column) {
                if (empty($column['name'])) continue;
                
                $colName = trim($column['name']);
                $colType = $column['type'];
                $colLength = !empty($column['length']) ? "({$column['length']})" : '';
                $colNull = isset($column['null']) ? 'NULL' : 'NOT NULL';
                $colDefault = !empty($column['default']) ? "DEFAULT '{$column['default']}'" : '';
                $colExtra = '';
                
                if (isset($column['primary'])) {
                    $colExtra .= ' PRIMARY KEY';
                }
                if (isset($column['auto_increment'])) {
                    $colExtra .= ' AUTO_INCREMENT';
                }
                
                $columnDefinitions[] = "`$colName` $colType$colLength $colNull $colDefault $colExtra";
            }
            
            if (empty($columnDefinitions)) {
                throw new Exception('მინიმუმ ერთი სვეტი უნდა იყოს განსაზღვრული');
            }
            
            $sql .= implode(', ', $columnDefinitions) . ")";
            
            $this->pdo->exec($sql);
            
            // Reload tables
            $this->loadTables();
            
            return [
                'success' => true,
                'message' => "ცხრილი '$tableName' წარმატებით შეიქმნა"
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'შეცდომა ცხრილის შექმნისას: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => $e->getMessage()
            ];
        }
    }
}
?>
