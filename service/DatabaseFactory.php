<?php
/**
 * service/DatabaseFactory.php
 * Smart Database Factory with Auto-Detection
 */

namespace PIMP\Services;

use PIMP\Services\Database\MySQLDatabase;
use PIMP\Services\Database\MongoDBDatabase;
use PIMP\Services\Database\RedisDatabase;
use PIMP\Services\Database\SQLiteDatabase;
use InvalidArgumentException;

class DatabaseFactory
{
    /**
     * @var array Cached database instances
     */
    private static $instances = [];

    /**
     * @var bool Enable auto-detection
     */
    private static $autoDetect = true;

    /**
     * Create database connection
     * 
     * @param string $driver Database driver (mysql, mongodb, redis, sqlite)
     * @param array|null $config Optional config override
     * @return mixed Database instance
     */
    public static function create(string $driver, ?array $config = null)
    {
        // Check cache
        $cacheKey = $driver . '_' . md5(serialize($config));
        if (isset(self::$instances[$cacheKey])) {
            return self::$instances[$cacheKey];
        }

        // Get configuration
        if ($config === null) {
            $config = self::getConfig($driver);
        }

        // Create instance based on driver
        $instance = match($driver) {
            'mysql' => new MySQLDatabase($config),
            'mongodb' => new MongoDBDatabase($config),
            'redis' => new RedisDatabase($config),
            'sqlite' => new SQLiteDatabase($config),
            default => throw new InvalidArgumentException("Unsupported database driver: {$driver}")
        };

        // Cache instance
        self::$instances[$cacheKey] = $instance;

        return $instance;
    }

    /**
     * Get configuration for a database driver
     * 
     * @param string $driver
     * @return array
     */
    private static function getConfig(string $driver): array
    {
        // Load base configuration
        $baseConfig = require __DIR__ . '/../config/database.php';
        
        if (!isset($baseConfig['connections'][$driver])) {
            throw new InvalidArgumentException("Configuration not found for driver: {$driver}");
        }

        $config = $baseConfig['connections'][$driver];

        // Auto-detect service location if enabled
        if (self::$autoDetect && ($baseConfig['auto_detect']['enabled'] ?? false)) {
            $detected = ServiceDetector::detectServiceHost($driver);
            
            if ($detected['accessible']) {
                $config['host'] = $detected['host'];
                $config['port'] = $detected['port'];
                $config['_detected_type'] = $detected['type'];
                
                // Log detection for debugging
                error_log("DatabaseFactory: Auto-detected {$driver} at {$detected['host']}:{$detected['port']} ({$detected['type']})");
            } else {
                error_log("DatabaseFactory: Warning - {$driver} not accessible at any configured host");
            }
        }

        return $config;
    }

    /**
     * Create MySQL connection
     */
    public static function mysql(?array $config = null): MySQLDatabase
    {
        return self::create('mysql', $config);
    }

    /**
     * Create MongoDB connection
     */
    public static function mongodb(?array $config = null): MongoDBDatabase
    {
        return self::create('mongodb', $config);
    }

    /**
     * Create Redis connection
     */
    public static function redis(?array $config = null): RedisDatabase
    {
        return self::create('redis', $config);
    }

    /**
     * Create SQLite connection
     */
    public static function sqlite(?array $config = null): SQLiteDatabase
    {
        return self::create('sqlite', $config);
    }

    /**
     * Get default database connection
     */
    public static function default()
    {
        $baseConfig = require __DIR__ . '/../config/database.php';
        $defaultDriver = $baseConfig['default'] ?? 'mysql';
        
        return self::create($defaultDriver);
    }

    /**
     * Enable or disable auto-detection
     */
    public static function setAutoDetect(bool $enabled): void
    {
        self::$autoDetect = $enabled;
    }

    /**
     * Clear cached instances
     */
    public static function clearCache(): void
    {
        self::$instances = [];
    }

    /**
     * Test all database connections
     */
    public static function testAll(): array
    {
        $results = [];
        $drivers = ['mysql', 'mongodb', 'redis', 'sqlite'];

        foreach ($drivers as $driver) {
            try {
                $db = self::create($driver);
                $connected = $db->isConnected();
                
                $results[$driver] = [
                    'status' => $connected ? 'connected' : 'failed',
                    'type' => $db->getConfig()['_detected_type'] ?? 'unknown',
                    'host' => $db->getConfig()['host'] ?? 'N/A',
                    'port' => $db->getConfig()['port'] ?? 'N/A',
                ];
            } catch (\Exception $e) {
                $results[$driver] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}