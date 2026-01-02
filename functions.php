<?php
// Example usage in admin/index.php
require_once 'vendor/autoload.php';

use PIMP\Services\Database\MySQLDatabase;
use PIMP\Services\Database\MongoDBDatabase;
use PIMP\Services\Database\RedisDatabase;
use PIMP\Services\Database\SQLiteDatabase;
use PIMP\Core\Config;

// Get configurations from your Config class
$mysqlConfig = [
    'host' => 'localhost',
    'port' => 3306,
    'username' => 'root',
    'password' => 'root',
    'database' => 'pimp_db'
];

$mysql = new MySQLDatabase($mysqlConfig);

// Execute inline query
$result = $mysql->query("SELECT * FROM users WHERE id = ?", [1]);

// Execute from file
$result = $mysql->executeFile('seeds/001_init.sql');

// Execute all migrations
$results = $mysql->migrateAll();

// Execute all seeds
$results = $mysql->seedAll();

// Create backup
// $backupPath = $mysql->backup();

// MongoDB example
// $mongo = new MongoDBDatabase($mongoConfig);
// $result = $mongo->executeFile('MongoDB/migrations/001_init.json');

// Redis example
// $redis = new RedisDatabase($redisConfig);
// $result = $redis->executeFile('Redis/seeds/001_cache_data.json');

// SQLite example
// $sqlite = new SQLiteDatabase($sqliteConfig);
// $result = $sqlite->executeMigration('001_init.sql');
