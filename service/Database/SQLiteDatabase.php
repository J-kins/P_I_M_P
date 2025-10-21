<?php
/**
 * P.I.M.P - SQLite Database Service with File Query Support
 */

namespace PIMP\Service\Database;

use PDO;
use PDOException;
use Exception;
use InvalidArgumentException;

class SQLiteDatabase extends Database
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
        return 'SQLite';
    }

    /**
     * Connect to SQLite database
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
            $database = $this->config['database'];
            
            // Determine if it's a file path or :memory:
            if ($database === ':memory:' || strpos($database, '/') === 0) {
                $dsn = "sqlite:{$database}";
            } else {
                // Relative path, ensure directory exists
                $directory = dirname($database);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                $dsn = "sqlite:{$database}";
            }

            $options = array_merge([
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ], $this->config['options'] ?? []);

            return new PDO($dsn, null, null, $options);
        });

        // Enable foreign keys and set journal mode
        $this->connection->exec('PRAGMA foreign_keys = ON');
        $this->connection->exec('PRAGMA journal_mode = WAL');
        $this->connection->exec('PRAGMA synchronous = NORMAL');

        $this->connected = true;
        return $this->connection;
    }

    /**
     * Disconnect from SQLite
     * 
     * @return bool
     */
    public function disconnect(): bool
    {
        $this->connection = null;
        $this->connected = false;
        return true;
    }

    /**
     * Check if connected to SQLite
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
        
        // SQLite doesn't support multiple statements in single query, so we split them
        $queryList = $this->parseSqlFile($queries);
        
        foreach ($queryList as $query) {
            if (empty(trim($query))) {
                continue;
            }
            
            try {
                $statement = $connection->query($query);
                $results[] = [
                    'query' => $query,
                    'success' => true,
                    'affected_rows' => $statement->rowCount(),
                    'result' => $statement->fetchAll(PDO::FETCH_ASSOC)
                ];
            } catch (PDOException $e) {
                $results[] = [
                    'query' => $query,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
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
     * Create backup of SQLite database
     * 
     * @param string $backupName Optional backup filename
     * @return string Path to backup file
     * @throws Exception
     */
    public function backup(string $backupName = null): string
    {
        $backupDir = $this->basePath . '/SQLite/backups/';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupName = $backupName ?: 'backup_' . date('Y-m-d_H-i-s') . '.sqlite';
        $backupPath = $backupDir . $backupName;

        // For SQLite, we can simply copy the database file
        $databaseFile = $this->config['database'];
        
        if ($databaseFile === ':memory:') {
            throw new Exception("Cannot backup in-memory SQLite database");
        }

        if (!file_exists($databaseFile)) {
            throw new Exception("SQLite database file not found: {$databaseFile}");
        }

        if (!copy($databaseFile, $backupPath)) {
            throw new Exception("Failed to create SQLite backup: {$backupPath}");
        }

        return $backupPath;
    }

    /**
     * Restore SQLite database from backup
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

        $databaseFile = $this->config['database'];
        
        if ($databaseFile === ':memory:') {
            throw new Exception("Cannot restore to in-memory SQLite database");
        }

        // Close current connection
        $this->disconnect();

        // Copy backup file to database location
        if (!copy($backupPath, $databaseFile)) {
            throw new Exception("Failed to restore SQLite database from backup");
        }

        // Reconnect
        $this->connect();

        return true;
    }

    /**
     * Escape identifier for SQLite
     * 
     * @param string $identifier
     * @return string
     */
    public function escapeIdentifier(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    /**
     * Validate SQLite configuration
     * 
     * @param array $config
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function validateConfig(array $config): bool
    {
        if (empty($config['database'])) {
            throw new InvalidArgumentException("SQLite configuration missing required key: database");
        }

        return true;
    }

    /**
     * Create DSN for SQLite
     * 
     * @param array $config
     * @return string
     */
    protected function createDsn(array $config): string
    {
        return "sqlite:{$config['database']}";
    }

    /**
     * Get SQLite version
     * 
     * @return string
     */
    public function getVersion(): string
    {
        return $this->fetchColumn('SELECT sqlite_version()');
    }

    /**
     * Check if table exists
     * 
     * @param string $table
     * @return bool
     */
    public function tableExists(string $table): bool
    {
        $result = $this->fetchOne(
            "SELECT name FROM sqlite_master WHERE type='table' AND name = ?",
            [$table]
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
        return $this->fetchAll("PRAGMA table_info(" . $this->escapeIdentifier($table) . ")");
    }

    /**
     * Get list of tables
     * 
     * @return array
     */
    public function getTables(): array
    {
        return $this->fetchAll(
            "SELECT name as table_name 
             FROM sqlite_master 
             WHERE type='table' 
             AND name NOT LIKE 'sqlite_%'
             ORDER BY name"
        );
    }
}