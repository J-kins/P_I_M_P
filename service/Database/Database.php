<?php
/**
 * P.I.M.P - Database Abstraction Layer
 * Core Database Service with File Query Support
 */

namespace PIMP\Services\Database;

use PIMP\Core\Config;
use PDO;
use PDOException;
use MongoDB\Client;
use Redis;
use RedisException;
use Exception;
use InvalidArgumentException;

abstract class Database
{
    /**
     * @var mixed Database connection instance
     */
    protected $connection;

    /**
     * @var string Database type
     */
    protected $type;

    /**
     * @var array Database configuration
     */
    protected $config;

    /**
     * @var bool Connection status
     */
    protected $connected = false;

    /**
     * @var string Base path for database files
     */
    protected $basePath;

    /**
     * @var array Default configuration
     */
    protected $defaultConfig = [
        'host' => 'localhost',
        'port' => null,
        'username' => null,
        'password' => null,
        'database' => null,
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'options' => [],
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 1000,
    ];

    /**
     * Database constructor.
     * 
     * @param array $config Database configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->defaultConfig, $config);
        $this->type = $this->getDatabaseType();
        $this->basePath = dirname(__DIR__, 2) . '/config/database';
    }

    /**
     * Get database type (must be implemented by child classes)
     * 
     * @return string
     */
    abstract protected function getDatabaseType(): string;

    /**
     * Connect to the database (must be implemented by child classes)
     * 
     * @return mixed
     */
    abstract public function connect();

    /**
     * Disconnect from the database (must be implemented by child classes)
     * 
     * @return bool
     */
    abstract public function disconnect(): bool;

    /**
     * Check if connected to database
     * 
     * @return bool
     */
    abstract public function isConnected(): bool;

    /**
     * Execute a query (must be implemented by child classes)
     * 
     * @param string $query
     * @param array $params
     * @return mixed
     */
    abstract public function query(string $query, array $params = []);

    /**
     * Execute multiple queries from a string
     * 
     * @param string $queries
     * @return array
     */
    abstract public function executeMultiple(string $queries): array;

    /**
     * Get last insert ID
     * 
     * @return string|int|null
     */
    abstract public function lastInsertId();

    /**
     * Begin transaction
     * 
     * @return bool
     */
    abstract public function beginTransaction(): bool;

    /**
     * Commit transaction
     * 
     * @return bool
     */
    abstract public function commit(): bool;

    /**
     * Rollback transaction
     * 
     * @return bool
     */
    abstract public function rollback(): bool;

    /**
     * Execute query from file
     * 
     * @param string $filePath Path to SQL file
     * @param array $params Parameters for prepared statements
     * @return mixed
     * @throws Exception
     */
    public function executeFile(string $filePath, array $params = [])
    {
        $absolutePath = $this->resolveFilePath($filePath);
        
        if (!file_exists($absolutePath)) {
            throw new Exception("Database file not found: {$absolutePath}");
        }

        $content = file_get_contents($absolutePath);
        
        if ($content === false) {
            throw new Exception("Failed to read database file: {$absolutePath}");
        }

        // Remove BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        
        // Trim and check if empty
        $content = trim($content);
        if (empty($content)) {
            throw new Exception("Database file is empty: {$absolutePath}");
        }

        return $this->executeMultiple($content);
    }

    /**
     * Execute migration file
     * 
     * @param string $migrationName Migration file name
     * @param array $params Parameters for prepared statements
     * @return mixed
     * @throws Exception
     */
    public function executeMigration(string $migrationName, array $params = [])
    {
        $filePath = $this->basePath . '/' . $this->getDatabaseType() . '/migrations/' . $migrationName;
        return $this->executeFile($filePath, $params);
    }

    /**
     * Execute seed file
     * 
     * @param string $seedName Seed file name
     * @param array $params Parameters for prepared statements
     * @return mixed
     * @throws Exception
     */
    public function executeSeed(string $seedName, array $params = [])
    {
        $filePath = $this->basePath . '/' . $this->getDatabaseType() . '/seeds/' . $seedName;
        return $this->executeFile($filePath, $params);
    }

    /**
     * Execute all migrations in order
     * 
     * @return array Results of all migrations
     * @throws Exception
     */
    public function migrateAll(): array
    {
        $migrationsPath = $this->basePath . '/' . $this->getDatabaseType() . '/migrations/';
        
        if (!is_dir($migrationsPath)) {
            throw new Exception("Migrations directory not found: {$migrationsPath}");
        }

        $files = glob($migrationsPath . '*.sql');
        if (empty($files)) {
            throw new Exception("No migration files found in: {$migrationsPath}");
        }

        // Sort files by name (assuming they start with numbers)
        sort($files);
        
        $results = [];
        foreach ($files as $file) {
            $filename = basename($file);
            $results[$filename] = $this->executeMigration($filename);
        }

        return $results;
    }

    /**
     * Execute all seeds in order
     * 
     * @return array Results of all seeds
     * @throws Exception
     */
    public function seedAll(): array
    {
        $seedsPath = $this->basePath . '/' . $this->getDatabaseType() . '/seeds/';
        
        if (!is_dir($seedsPath)) {
            throw new Exception("Seeds directory not found: {$seedsPath}");
        }

        $files = glob($seedsPath . '*.sql');
        if (empty($files)) {
            throw new Exception("No seed files found in: {$seedsPath}");
        }

        // Sort files by name
        sort($files);
        
        $results = [];
        foreach ($files as $file) {
            $filename = basename($file);
            $results[$filename] = $this->executeSeed($filename);
        }

        return $results;
    }

    /**
     * Create backup of database
     * 
     * @param string $backupName Optional backup filename
     * @return string Path to backup file
     * @throws Exception
     */
    abstract public function backup(string $backupName = null): string;

    /**
     * Restore database from backup
     * 
     * @param string $backupPath Path to backup file
     * @return bool
     * @throws Exception
     */
    abstract public function restore(string $backupPath): bool;

    /**
     * Resolve file path - supports absolute and relative paths
     * 
     * @param string $filePath
     * @return string
     */
    protected function resolveFilePath(string $filePath): string
    {
        // If it's already an absolute path, return as is
        if (strpos($filePath, '/') === 0) {
            return $filePath;
        }

        // If it contains database type, assume it's relative to base path
        if (strpos($filePath, $this->getDatabaseType()) !== false) {
            return $this->basePath . '/' . $filePath;
        }

        // Otherwise, assume it's relative to the specific database type directory
        return $this->basePath . '/' . $this->getDatabaseType() . '/' . $filePath;
    }

    /**
     * Parse SQL file and split into individual queries
     * 
     * @param string $sql
     * @return array
     */
    protected function parseSqlFile(string $sql): array
    {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon, but ignore semicolons in quotes
        $queries = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if (($char === "'" || $char === '"') && ($i === 0 || $sql[$i - 1] !== '\\')) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar) {
                    $inString = false;
                }
            }
            
            $current .= $char;
            
            if ($char === ';' && !$inString) {
                $query = trim($current);
                if (!empty($query)) {
                    $queries[] = $query;
                }
                $current = '';
            }
        }
        
        // Add the last query if not empty
        $lastQuery = trim($current);
        if (!empty($lastQuery)) {
            $queries[] = $lastQuery;
        }
        
        return array_filter($queries);
    }

    /**
     * Get database connection instance
     * 
     * @return mixed
     */
    public function getConnection()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Get database type
     * 
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get configuration
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set configuration
     * 
     * @param array $config
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Ping database connection
     * 
     * @return bool
     */
    public function ping(): bool
    {
        try {
            return $this->isConnected();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Reconnect to database
     * 
     * @return bool
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        return $this->connect() !== null;
    }

    /**
     * Get database statistics
     * 
     * @return array
     */
    public function getStats(): array
    {
        return [
            'type' => $this->type,
            'connected' => $this->connected,
            'config' => [
                'host' => $this->config['host'],
                'port' => $this->config['port'],
                'database' => $this->config['database'],
            ],
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Validate database configuration
     * 
     * @param array $config
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function validateConfig(array $config): bool
    {
        $required = ['host', 'database'];
        
        foreach ($required as $key) {
            if (empty($config[$key])) {
                throw new InvalidArgumentException("Database configuration missing required key: {$key}");
            }
        }

        return true;
    }

    /**
     * Create DSN string for PDO connections
     * 
     * @param array $config
     * @return string
     */
    protected function createDsn(array $config): string
    {
        $dsn = "{$this->type}:host={$config['host']}";

        if (!empty($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        if (!empty($config['database'])) {
            $dsn .= ";dbname={$config['database']}";
        }

        if (!empty($config['charset'])) {
            $dsn .= ";charset={$config['charset']}";
        }

        return $dsn;
    }

    /**
     * Handle connection errors with retry logic
     * 
     * @param callable $connectionAttempt
     * @return mixed
     * @throws Exception
     */
    protected function connectWithRetry(callable $connectionAttempt)
    {
        $lastException = null;
        $attempts = $this->config['retry_attempts'];

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $connectionAttempt();
            } catch (Exception $e) {
                $lastException = $e;
                
                if ($attempt < $attempts) {
                    usleep($this->config['retry_delay'] * 1000);
                    error_log("Database connection attempt {$attempt} failed: " . $e->getMessage());
                }
            }
        }

        throw new Exception(
            "Failed to connect to {$this->type} database after {$attempts} attempts: " . 
            $lastException->getMessage(),
            0,
            $lastException
        );
    }

    /**
     * Escape identifier (table/column names)
     * 
     * @param string $identifier
     * @return string
     */
    public function escapeIdentifier(string $identifier): string
    {
        return $identifier;
    }

    /**
     * Get table name with prefix
     * 
     * @param string $table
     * @return string
     */
    public function table(string $table): string
    {
        $prefix = $this->config['prefix'] ?? '';
        return $prefix . $table;
    }

    /**
     * Destructor - ensure connection is closed
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}