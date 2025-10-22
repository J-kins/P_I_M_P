<?php
/**
 * P.I.M.P - MySQL Database Service with File Query Support
 */

namespace PIMP\Services\Database;

use PDO;
use PDOException;
use Exception;

class MySQLDatabase extends Database
{
    /**
     * @var PDO PDO connection instance
     */
    protected $connection;

    /**
     * Get database type
     * 
     * @return string
     */
    protected function getDatabaseType(): string
    {
        return 'MySQL';
    }

    /**
     * Connect to MySQL database
     * 
     * @return PDO
     * @throws Exception
     */
    public function connect(): PDO
    {
        if ($this->connected && $this->connection instanceof PDO) {
            return $this->connection;
        }

        $this->validateConfig($this->config);

        $this->connection = $this->connectWithRetry(function () {
            $dsn = 'mysql:dbname=pimp_db;host=127.0.0.1';
            
            $options = array_merge([
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->config['charset']} COLLATE {$this->config['collation']}",
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            ], $this->config['options'] ?? []);

            return new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );
        });

        $this->connected = true;
        
        // Set timezone if specified
        if (!empty($this->config['timezone'])) {
            $this->connection->exec("SET time_zone = '{$this->config['timezone']}'");
        }

        return $this->connection;
    }

    /**
     * Disconnect from MySQL database
     * 
     * @return bool
     */
    public function disconnect(): bool
    {
        $this->connection = null;
        $this->connected = false;
        return true;
    }

    /**g
     * Check if connected to MySQL
     * 
     * @return bool
     */
    public function isConnected(): bool
    {
        try {
            if ($this->connection instanceof PDO) {
                $this->connection->query('SELECT 1');
                return true;
            }
        } catch (PDOException $e) {
            $this->connected = false;
        }
        
        return false;
    }

    /**
     * Execute a query
     * 
     * @param string $query
     * @param array $params
     * @return \PDOStatement
     * @throws PDOException
     */
    public function query(string $query, array $params = [])
    {
        $connection = $this->getConnection();
        $statement = $connection->prepare($query);
        
        if (!$statement) {
            throw new PDOException("Failed to prepare query: {$query}");
        }

        $statement->execute($params);
        return $statement;
    }

    /**
     * Execute multiple queries from a string
     * 
     * @param string $queries
     * @return array
     */
    public function executeMultiple(string $queries): array
    {
        $connection = $this->getConnection();
        $results = [];
        
        // Split queries
        $queryList = $this->parseSqlFile($queries);
        
        foreach ($queryList as $query) {
            if (empty(trim($query))) {
                continue;
            }
            
            try {
                $statement = $connection->query($query);
                
                // Check if query execution failed
                if ($statement === false) {
                    $errorInfo = $connection->errorInfo();
                    throw new PDOException(
                        "Query execution failed: " . ($errorInfo[2] ?? 'Unknown error')
                    );
                }
                
                $affectedRows = $statement->rowCount();
                
                // For SELECT queries, fetch results
                if (stripos(trim($query), 'SELECT') === 0) {
                    $resultData = $statement->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $resultData = null;
                }
                
                $results[] = [
                    'query' => $query,
                    'success' => true,
                    'affected_rows' => $affectedRows,
                    'result' => $resultData
                ];
                
            } catch (PDOException $e) {
                $results[] = [
                    'query' => $query,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                // Don't stop execution, continue with next query
            }
        }
        
        return $results;
    }

    /**
     * Execute a query and return all results
     * 
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchAll(string $query, array $params = []): array
    {
        $statement = $this->query($query, $params);
        return $statement->fetchAll();
    }

    /**
     * Execute a query and return first result
     * 
     * @param string $query
     * @param array $params
     * @return array|null
     */
    public function fetchOne(string $query, array $params = []): ?array
    {
        $statement = $this->query($query, $params);
        $result = $statement->fetch();
        return $result ?: null;
    }

    /**
     * Execute a query and return a single value
     * 
     * @param string $query
     * @param array $params
     * @return mixed
     */
    public function fetchColumn(string $query, array $params = [])
    {
        $statement = $this->query($query, $params);
        return $statement->fetchColumn();
    }

    /**
     * Get last insert ID
     * 
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }

    /**
     * Create backup of MySQL database
     * 
     * @param string $backupName Optional backup filename
     * @return string Path to backup file
     * @throws Exception
     */
    public function backup(string $backupName = null): string
    {
        $backupDir = $this->basePath . '/MySQL/backups/';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupName = $backupName ?: 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backupPath = $backupDir . $backupName;

        // Use mysqldump for backup (you might need to adjust the path)
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($this->config['host']),
            escapeshellarg($this->config['port'] ?? '3306'),
            escapeshellarg($this->config['username']),
            escapeshellarg($this->config['password']),
            escapeshellarg($this->config['database']),
            escapeshellarg($backupPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("MySQL backup failed with code: {$returnCode}");
        }

        if (!file_exists($backupPath)) {
            throw new Exception("Backup file was not created: {$backupPath}");
        }

        return $backupPath;
    }

    /**
     * Restore MySQL database from backup
     * 
     * @param string $backupPath Path to backup file
     * @return bool
     * @throws Exception
     */
    public function restore(string $backupPath): bool
    {
        if (!file_exists($backupPath)) {
            throw new Exception("Backup file not found: {$backupPath}");
        }

        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($this->config['host']),
            escapeshellarg($this->config['port'] ?? '3306'),
            escapeshellarg($this->config['username']),
            escapeshellarg($this->config['password']),
            escapeshellarg($this->config['database']),
            escapeshellarg($backupPath)
        );

        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * Escape identifier for MySQL
     * 
     * @param string $identifier
     * @return string
     */
    public function escapeIdentifier(string $identifier): string
    {
        return "`" . str_replace("`", "``", $identifier) . "`";
    }

    /**
     * Get MySQL version
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return $this->fetchColumn('SELECT VERSION()');
    }

    /**
     * Check if table exists
     * 
     * @param string $table
     * @return bool
     */
    public function tableExists(string $table): bool
    {
        $database = $this->config['database'];
        $result = $this->fetchOne(
            "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
            [$database, $table]
        );
        
        return !empty($result);
    }

    /**
     * Get table structure
     * 
     * @param string $table
     * @return array
     */
    public function getTableStructure(string $table): array
    {
        return $this->fetchAll("DESCRIBE " . $this->escapeIdentifier($table));
    }

    /**
     * Get list of tables
     * 
     * @return array
     */
    public function getTables(): array
    {
        $database = $this->config['database'];
        return $this->fetchAll(
            "SELECT TABLE_NAME as table_name 
             FROM INFORMATION_SCHEMA.TABLES 
             WHERE TABLE_SCHEMA = ? 
             ORDER BY TABLE_NAME",
            [$database]
        );
    }
}