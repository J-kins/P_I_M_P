
<?php
/**
 * Database utility functions for CRUD operations
 * This file handles AJAX requests from JavaScript
 */

// Set headers for JSON response
header('Content-Type: application/json');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'app_database');

/**
 * Get database connection
 * 
 * @return PDO The database connection object
 */
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // In production, you'd want to log this instead
        die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
    }
}

/**
 * Validate table name to prevent SQL injection
 * 
 * @param string $table Table name to validate
 * @return string Validated table name
 */
function validateTable($table) {
    // Only allow alphanumeric characters and underscores
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        die(json_encode(['error' => 'Invalid table name']));
    }
    return $table;
}

/**
 * Handle the request based on the action parameter
 */
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'fetchAll':
        $table = validateTable($_GET['table'] ?? '');
        
        // Build query with optional filters
        $db = getDbConnection();
        $query = "SELECT * FROM {$table}";
        $params = [];
        
        // Add filters if provided
        $filters = array_diff_key($_GET, ['action' => '', 'table' => '']);
        if (!empty($filters)) {
            $query .= " WHERE";
            $conditions = [];
            
            foreach ($filters as $field => $value) {
                if (preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
                    $conditions[] = "{$field} = ?";
                    $params[] = $value;
                }
            }
            
            $query .= " " . implode(' AND ', $conditions);
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;
        
    case 'fetchById':
        $table = validateTable($_GET['table'] ?? '');
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            die(json_encode(['error' => 'ID is required']));
        }
        
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch();
        
        if (!$record) {
            http_response_code(404);
            echo json_encode(['error' => 'Record not found']);
        } else {
            echo json_encode($record);
        }
        break;
        
    case 'create':
        $table = validateTable($_POST['table'] ?? '');
        $data = json_decode($_POST['data'] ?? '{}', true);
        
        if (empty($data)) {
            die(json_encode(['error' => 'No data provided']));
        }
        
        $db = getDbConnection();
        
        // Build query
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $columnsStr = implode(', ', array_map(function($col) {
            return preg_match('/^[a-zA-Z0-9_]+$/', $col) ? $col : null;
        }, $columns));
        
        if (strpos($columnsStr, 'null') !== false) {
            die(json_encode(['error' => 'Invalid column name']));
        }
        
        $query = "INSERT INTO {$table} ({$columnsStr}) VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($query);
        $stmt->execute(array_values($data));
        
        $newId = $db->lastInsertId();
        
        // Fetch the newly created record
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$newId]);
        echo json_encode($stmt->fetch());
        break;
        
    case 'update':
        $table = validateTable($_POST['table'] ?? '');
        $id = $_POST['id'] ?? null;
        $data = json_decode($_POST['data'] ?? '{}', true);
        
        if (!$id) {
            die(json_encode(['error' => 'ID is required']));
        }
        
        if (empty($data)) {
            die(json_encode(['error' => 'No data provided']));
        }
        
        $db = getDbConnection();
        
        // Build query
        $setClause = implode(', ', array_map(function($col) {
            return preg_match('/^[a-zA-Z0-9_]+$/', $col) ? "{$col} = ?" : null;
        }, array_keys($data)));
        
        if (strpos($setClause, 'null') !== false) {
            die(json_encode(['error' => 'Invalid column name']));
        }
        
        $query = "UPDATE {$table} SET {$setClause} WHERE id = ?";
        $stmt = $db->prepare($query);
        
        // Add ID as the last parameter
        $params = array_values($data);
        $params[] = $id;
        
        $stmt->execute($params);
        
        // Fetch the updated record
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch());
        break;
        
    case 'delete':
        $table = validateTable($_POST['table'] ?? '');
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            die(json_encode(['error' => 'ID is required']));
        }
        
        $db = getDbConnection();
        $stmt = $db->prepare("DELETE FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode(['success' => true, 'deleted_rows' => $stmt->rowCount()]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
