<?php
/**
 * P.I.M.P - Redis Database Service with File Query Support
 */

namespace PIMP\Service\Database;

use Redis;
use RedisException;
use Exception;

class RedisDatabase extends Database
{
    /**
     * @var Redis Redis connection instance
     */
    protected $connection;

    /**
     * Get database type
     * 
     * @return string
     */
    protected function getDatabaseType(): string
    {
        return 'Redis';
    }

    /**
     * Connect to Redis
     * 
     * @return Redis
     * @throws Exception
     */
    public function connect(): Redis
    {
        if ($this->connected && $this->connection instanceof Redis) {
            return $this->connection;
        }

        $this->validateConfig($this->config);

        $this->connection = $this->connectWithRetry(function () {
            $redis = new Redis();
            
            $host = $this->config['host'];
            $port = $this->config['port'] ?? 6379;
            $timeout = $this->config['timeout'] ?? 30;
            $password = $this->config['password'] ?? null;
            $database = $this->config['database'] ?? 0;

            // Connect to Redis
            if ($this->config['persistent'] ?? false) {
                $connected = $redis->pconnect($host, $port, $timeout);
            } else {
                $connected = $redis->connect($host, $port, $timeout);
            }

            if (!$connected) {
                throw new RedisException("Failed to connect to Redis server");
            }

            // Authenticate if password is provided
            if ($password && !$redis->auth($password)) {
                throw new RedisException("Redis authentication failed");
            }

            // Select database
            if ($database && !$redis->select($database)) {
                throw new RedisException("Failed to select Redis database: {$database}");
            }

            // Set options
            $options = $this->config['options'] ?? [];
            foreach ($options as $option => $value) {
                $redis->setOption($option, $value);
            }

            return $redis;
        });

        $this->connected = true;
        return $this->connection;
    }

    /**
     * Disconnect from Redis
     * 
     * @return bool
     */
    public function disconnect(): bool
    {
        if ($this->connection instanceof Redis) {
            $this->connection->close();
        }
        
        $this->connection = null;
        $this->connected = false;
        return true;
    }

    /**
     * Check if connected to Redis
     * 
     * @return bool
     */
    public function isConnected(): bool
    {
        try {
            if ($this->connection instanceof Redis) {
                return $this->connection->ping() === '+PONG';
            }
        } catch (RedisException $e) {
            $this->connected = false;
        }
        
        return false;
    }

    /**
     * Execute Redis command
     * 
     * @param string $command
     * @param array $params
     * @return mixed
     * @throws RedisException
     */
    public function query(string $command, array $params = [])
    {
        $connection = $this->getConnection();
        return call_user_func_array([$connection, $command], $params);
    }

    /**
     * Execute multiple Redis commands from a string
     * 
     * @param string $commands JSON string with Redis commands
     * @return array
     */
    public function executeMultiple(string $commands): array
    {
        $connection = $this->getConnection();
        $operations = json_decode($commands, true);
        $results = [];

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid JSON in Redis operations: " . json_last_error_msg());
        }

        // Start pipeline for better performance
        $connection->multi();

        foreach ($operations as $operation) {
            try {
                $command = $operation['command'] ?? null;
                $args = $operation['args'] ?? [];

                if (!$command) {
                    throw new InvalidArgumentException("Redis operation missing command");
                }

                if (!method_exists($connection, $command)) {
                    throw new InvalidArgumentException("Invalid Redis command: {$command}");
                }

                call_user_func_array([$connection, $command], $args);
                
                $results[] = [
                    'command' => $command,
                    'args' => $args,
                    'success' => true
                ];
            } catch (RedisException $e) {
                $results[] = [
                    'command' => $command ?? 'unknown',
                    'args' => $args ?? [],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Execute pipeline
        $pipelineResults = $connection->exec();
        
        // Combine results with pipeline execution results
        foreach ($results as $index => &$result) {
            if ($result['success'] && isset($pipelineResults[$index])) {
                $result['result'] = $pipelineResults[$index];
            }
        }

        return $results;
    }

    /**
     * Execute Redis commands from file
     * 
     * @param string $filePath Path to JSON file with Redis commands
     * @return array
     * @throws Exception
     */
    public function executeFile(string $filePath, array $params = []): array
    {
        $absolutePath = $this->resolveFilePath($filePath);
        
        if (!file_exists($absolutePath)) {
            throw new Exception("Redis commands file not found: {$absolutePath}");
        }

        $content = file_get_contents($absolutePath);
        
        if ($content === false) {
            throw new Exception("Failed to read Redis commands file: {$absolutePath}");
        }

        return $this->executeMultiple($content);
    }

    /**
     * Get last insert ID (not applicable for Redis)
     * 
     * @return null
     */
    public function lastInsertId()
    {
        return null;
    }

    /**
     * Begin transaction (Redis MULTI)
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->connection->multi();
    }

    /**
     * Commit transaction (Redis EXEC)
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection->exec() !== false;
    }

    /**
     * Rollback transaction (Redis DISCARD)
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->connection->discard();
    }

    /**
     * Create backup of Redis database
     * 
     * @param string $backupName Optional backup filename
     * @return string Path to backup file
     * @throws Exception
     */
    public function backup(string $backupName = null): string
    {
        $backupDir = $this->basePath . '/Redis/backups/';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupName = $backupName ?: 'backup_' . date('Y-m-d_H-i-s') . '.rdb';
        $backupPath = $backupDir . $backupName;

        // For Redis, we trigger a BGSAVE and wait for it to complete
        $connection = $this->getConnection();
        
        // Get the Redis data directory
        $config = $connection->config('GET', 'dir');
        $dataDir = $config['dir'] ?? '/var/lib/redis';
        
        $dbfilename = $connection->config('GET', 'dbfilename');
        $dbFile = $dbfilename['dbfilename'] ?? 'dump.rdb';

        $sourcePath = $dataDir . '/' . $dbFile;

        if (!file_exists($sourcePath)) {
            throw new Exception("Redis dump file not found: {$sourcePath}");
        }

        if (!copy($sourcePath, $backupPath)) {
            throw new Exception("Failed to create Redis backup: {$backupPath}");
        }

        return $backupPath;
    }

    /**
     * Restore Redis database from backup
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

        $connection = $this->getConnection();
        
        // Get the Redis data directory
        $config = $connection->config('GET', 'dir');
        $dataDir = $config['dir'] ?? '/var/lib/redis';
        
        $dbfilename = $connection->config('GET', 'dbfilename');
        $dbFile = $dbfilename['dbfilename'] ?? 'dump.rdb';

        $destinationPath = $dataDir . '/' . $dbFile;

        // Stop Redis, replace the file, and restart (this is simplified)
        // In production, you'd need to handle this more carefully
        if (!copy($backupPath, $destinationPath)) {
            throw new Exception("Failed to restore Redis database from backup");
        }

        // Note: In a real scenario, you'd need to restart Redis service
        // This is a simplified version
        return true;
    }

    /**
     * Set key-value pair
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public function set(string $key, $value, int $ttl = 0): bool
    {
        $connection = $this->getConnection();
        
        if ($ttl > 0) {
            return $connection->setex($key, $ttl, $value);
        } else {
            return $connection->set($key, $value);
        }
    }

    /**
     * Get value by key
     * 
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $connection = $this->getConnection();
        return $connection->get($key);
    }

    /**
     * Delete key
     * 
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $connection = $this->getConnection();
        return $connection->del($key) > 0;
    }

    /**
     * Check if key exists
     * 
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        $connection = $this->getConnection();
        return $connection->exists($key);
    }

    /**
     * Set key expiration
     * 
     * @param string $key
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public function expire(string $key, int $ttl): bool
    {
        $connection = $this->getConnection();
        return $connection->expire($key, $ttl);
    }

    /**
     * Get key time to live
     * 
     * @param string $key
     * @return int
     */
    public function ttl(string $key): int
    {
        $connection = $this->getConnection();
        return $connection->ttl($key);
    }

    /**
     * Increment key value
     * 
     * @param string $key
     * @param int $increment
     * @return int
     */
    public function increment(string $key, int $increment = 1): int
    {
        $connection = $this->getConnection();
        return $connection->incrBy($key, $increment);
    }

    /**
     * Decrement key value
     * 
     * @param string $key
     * @param int $decrement
     * @return int
     */
    public function decrement(string $key, int $decrement = 1): int
    {
        $connection = $this->getConnection();
        return $connection->decrBy($key, $decrement);
    }

    /**
     * Get all keys matching pattern
     * 
     * @param string $pattern
     * @return array
     */
    public function keys(string $pattern = '*'): array
    {
        $connection = $this->getConnection();
        return $connection->keys($pattern);
    }

    /**
     * Flush database
     * 
     * @return bool
     */
    public function flush(): bool
    {
        $connection = $this->getConnection();
        return $connection->flushDB();
    }

    /**
     * Get Redis info
     * 
     * @return array
     */
    public function info(): array
    {
        $connection = $this->getConnection();
        return $connection->info();
    }
}