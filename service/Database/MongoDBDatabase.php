<?php
/**
 * P.I.M.P - MongoDB Database Service with File Query Support
 */

namespace PIMP\Service\Database;

use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Collection;
use MongoDB\Driver\Exception\Exception as MongoDBException;
use Exception;
use InvalidArgumentException;

class MongoDBDatabase extends Database
{
    /**
     * @var Client MongoDB client
     */
    protected $connection;

    /**
     * @var Database MongoDB database instance
     */
    protected $database;

    /**
     * Get database type
     * 
     * @return string
     */
    protected function getDatabaseType(): string
    {
        return 'MongoDB';
    }

    /**
     * Connect to MongoDB
     * 
     * @return Client
     * @throws Exception
     */
    public function connect(): Client
    {
        if ($this->connected && $this->connection instanceof Client) {
            return $this->connection;
        }

        $this->validateConfig($this->config);

        $this->connection = $this->connectWithRetry(function () {
            $host = $this->config['host'];
            $port = $this->config['port'] ?? 27017;
            $username = $this->config['username'];
            $password = $this->config['password'];
            $database = $this->config['database'];
            
            // Build connection string
            $dsn = "mongodb://";
            
            if ($username && $password) {
                $dsn .= urlencode($username) . ':' . urlencode($password) . '@';
            }
            
            $dsn .= $host . ':' . $port;
            
            if ($database) {
                $dsn .= '/' . $database;
            }

            $options = array_merge([
                'connectTimeoutMS' => $this->config['timeout'] * 1000,
                'socketTimeoutMS' => $this->config['timeout'] * 1000,
                'serverSelectionTimeoutMS' => $this->config['timeout'] * 1000,
            ], $this->config['options'] ?? []);

            return new Client($dsn, $options);
        });

        // Select database
        if (!empty($this->config['database'])) {
            $this->database = $this->connection->selectDatabase($this->config['database']);
        }

        $this->connected = true;
        return $this->connection;
    }

    /**
     * Disconnect from MongoDB
     * 
     * @return bool
     */
    public function disconnect(): bool
    {
        // MongoDB client doesn't have explicit disconnect in PHP driver
        $this->connection = null;
        $this->database = null;
        $this->connected = false;
        return true;
    }

    /**
     * Check if connected to MongoDB
     * 
     * @return bool
     */
    public function isConnected(): bool
    {
        try {
            if ($this->connection instanceof Client) {
                $this->connection->listDatabases();
                return true;
            }
        } catch (MongoDBException $e) {
            $this->connected = false;
        }
        
        return false;
    }

    /**
     * Execute a MongoDB command
     * 
     * @param string $command Command name or collection method
     * @param array $params Parameters for the command
     * @return mixed
     * @throws MongoDBException
     */
    public function query(string $command, array $params = [])
    {
        $this->getConnection();
        
        if (strpos($command, '.') !== false) {
            // Collection operation: collectionName.method
            [$collectionName, $method] = explode('.', $command, 2);
            $collection = $this->database->selectCollection($collectionName);
            
            if (!method_exists($collection, $method)) {
                throw new InvalidArgumentException("Method {$method} does not exist on MongoDB collection");
            }
            
            return call_user_func_array([$collection, $method], $params);
        } else {
            // Database command
            return $this->database->command([$command => $params]);
        }
    }

    /**
     * Execute multiple queries from a JSON file
     * 
     * @param string $queries JSON string with operations
     * @return array
     */
    public function executeMultiple(string $queries): array
    {
        $this->getConnection();
        $operations = json_decode($queries, true);
        $results = [];

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid JSON in MongoDB operations: " . json_last_error_msg());
        }

        foreach ($operations as $operation) {
            try {
                $type = $operation['type'] ?? 'unknown';
                $collection = $operation['collection'] ?? null;
                $data = $operation['data'] ?? [];
                $options = $operation['options'] ?? [];

                if (!$collection) {
                    throw new InvalidArgumentException("MongoDB operation missing collection");
                }

                $collectionObj = $this->database->selectCollection($collection);

                switch ($type) {
                    case 'insert':
                        $result = $collectionObj->insertMany($data, $options);
                        $results[] = [
                            'type' => $type,
                            'collection' => $collection,
                            'success' => true,
                            'inserted_count' => $result->getInsertedCount(),
                            'inserted_ids' => $result->getInsertedIds()
                        ];
                        break;

                    case 'update':
                        $filter = $data['filter'] ?? [];
                        $update = $data['update'] ?? [];
                        $result = $collectionObj->updateMany($filter, $update, $options);
                        $results[] = [
                            'type' => $type,
                            'collection' => $collection,
                            'success' => true,
                            'matched_count' => $result->getMatchedCount(),
                            'modified_count' => $result->getModifiedCount()
                        ];
                        break;

                    case 'delete':
                        $filter = $data['filter'] ?? [];
                        $result = $collectionObj->deleteMany($filter, $options);
                        $results[] = [
                            'type' => $type,
                            'collection' => $collection,
                            'success' => true,
                            'deleted_count' => $result->getDeletedCount()
                        ];
                        break;

                    case 'createIndex':
                        $keys = $data['keys'] ?? [];
                        $result = $collectionObj->createIndex($keys, $options);
                        $results[] = [
                            'type' => $type,
                            'collection' => $collection,
                            'success' => true,
                            'index_name' => $result
                        ];
                        break;

                    default:
                        throw new InvalidArgumentException("Unsupported MongoDB operation type: {$type}");
                }
            } catch (MongoDBException $e) {
                $results[] = [
                    'type' => $type ?? 'unknown',
                    'collection' => $collection ?? 'unknown',
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Execute MongoDB operations from file
     * 
     * @param string $filePath Path to JSON file with operations
     * @return array
     * @throws Exception
     */
    public function executeFile(string $filePath, array $params = []): array
    {
        $absolutePath = $this->resolveFilePath($filePath);
        
        if (!file_exists($absolutePath)) {
            throw new Exception("MongoDB operations file not found: {$absolutePath}");
        }

        $content = file_get_contents($absolutePath);
        
        if ($content === false) {
            throw new Exception("Failed to read MongoDB operations file: {$absolutePath}");
        }

        return $this->executeMultiple($content);
    }

    /**
     * Get last insert ID (MongoDB uses ObjectId)
     * 
     * @return string|null
     */
    public function lastInsertId()
    {
        // In MongoDB, the _id is generated before insert
        return null;
    }

    /**
     * Begin transaction (MongoDB 4.0+)
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        try {
            $session = $this->connection->startSession();
            $session->startTransaction();
            return true;
        } catch (MongoDBException $e) {
            return false;
        }
    }

    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit(): bool
    {
        try {
            $session = $this->connection->startSession();
            $session->commitTransaction();
            return true;
        } catch (MongoDBException $e) {
            return false;
        }
    }

    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        try {
            $session = $this->connection->startSession();
            $session->abortTransaction();
            return true;
        } catch (MongoDBException $e) {
            return false;
        }
    }

    /**
     * Create backup of MongoDB database
     * 
     * @param string $backupName Optional backup filename
     * @return string Path to backup directory
     * @throws Exception
     */
    public function backup(string $backupName = null): string
    {
        $backupDir = $this->basePath . '/MongoDB/backups/';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupName = $backupName ?: 'backup_' . date('Y-m-d_H-i-s');
        $backupPath = $backupDir . $backupName;

        // Use mongodump for backup
        $command = sprintf(
            'mongodump --host=%s --port=%s --username=%s --password=%s --db=%s --out=%s',
            escapeshellarg($this->config['host']),
            escapeshellarg($this->config['port'] ?? '27017'),
            escapeshellarg($this->config['username']),
            escapeshellarg($this->config['password']),
            escapeshellarg($this->config['database']),
            escapeshellarg($backupPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("MongoDB backup failed with code: {$returnCode}");
        }

        if (!is_dir($backupPath)) {
            throw new Exception("Backup directory was not created: {$backupPath}");
        }

        return $backupPath;
    }

    /**
     * Restore MongoDB database from backup
     * 
     * @param string $backupPath Path to backup directory
     * @return bool
     * @throws Exception
     */
    public function restore(string $backupPath): bool
    {
        if (!is_dir($backupPath)) {
            throw new Exception("Backup directory not found: {$backupPath}");
        }

        // Use mongorestore for restoration
        $command = sprintf(
            'mongorestore --host=%s --port=%s --username=%s --password=%s --db=%s %s',
            escapeshellarg($this->config['host']),
            escapeshellarg($this->config['port'] ?? '27017'),
            escapeshellarg($this->config['username']),
            escapeshellarg($this->config['password']),
            escapeshellarg($this->config['database']),
            escapeshellarg($backupPath . '/' . $this->config['database'])
        );

        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    /**
     * Get collection instance
     * 
     * @param string $collectionName
     * @return Collection
     */
    public function collection(string $collectionName): Collection
    {
        $this->getConnection();
        return $this->database->selectCollection($collectionName);
    }

    /**
     * Insert document
     * 
     * @param string $collection
     * @param array $document
     * @return mixed
     */
    public function insert(string $collection, array $document)
    {
        return $this->collection($collection)->insertOne($document);
    }

    /**
     * Find documents
     * 
     * @param string $collection
     * @param array $filter
     * @param array $options
     * @return iterable
     */
    public function find(string $collection, array $filter = [], array $options = []): iterable
    {
        return $this->collection($collection)->find($filter, $options);
    }

    /**
     * Find one document
     * 
     * @param string $collection
     * @param array $filter
     * @param array $options
     * @return array|null
     */
    public function findOne(string $collection, array $filter = [], array $options = []): ?array
    {
        $result = $this->collection($collection)->findOne($filter, $options);
        return $result ? (array) $result : null;
    }

    /**
     * Update documents
     * 
     * @param string $collection
     * @param array $filter
     * @param array $update
     * @param array $options
     * @return mixed
     */
    public function update(string $collection, array $filter, array $update, array $options = [])
    {
        return $this->collection($collection)->updateMany($filter, ['$set' => $update], $options);
    }

    /**
     * Delete documents
     * 
     * @param string $collection
     * @param array $filter
     * @param array $options
     * @return mixed
     */
    public function delete(string $collection, array $filter, array $options = [])
    {
        return $this->collection($collection)->deleteMany($filter, $options);
    }

    /**
     * Count documents
     * 
     * @param string $collection
     * @param array $filter
     * @return int
     */
    public function count(string $collection, array $filter = []): int
    {
        return $this->collection($collection)->countDocuments($filter);
    }

    /**
     * Create index
     * 
     * @param string $collection
     * @param array $keys
     * @param array $options
     * @return string
     */
    public function createIndex(string $collection, array $keys, array $options = []): string
    {
        return $this->collection($collection)->createIndex($keys, $options);
    }

    /**
     * List collections
     * 
     * @return array
     */
    public function listCollections(): array
    {
        $this->getConnection();
        return iterator_to_array($this->database->listCollections());
    }

    /**
     * Validate MongoDB configuration
     * 
     * @param array $config
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function validateConfig(array $config): bool
    {
        parent::validateConfig($config);

        if (empty($config['database'])) {
            throw new InvalidArgumentException("MongoDB configuration missing required key: database");
        }

        return true;
    }
}