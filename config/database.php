<?php
/**
 * config/database.php
 * Flexible Database Configuration - Works with Docker and Native installations
 */

return [
    // Default connection (can be overridden)
    'default' => getenv('DB_CONNECTION') ?: 'mysql',
    
    // Service detection mode
    'mode' => getenv('SERVICE_MODE') ?: 'docker', // docker, native, hybrid
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('MYSQL_HOST') ?: 'mysql',
            'port' => getenv('MYSQL_PORT') ?: 3306,
            'database' => getenv('MYSQL_DATABASE') ?: 'pimp_db',
            'username' => getenv('MYSQL_USER') ?: 'pimp_user',
            'password' => getenv('MYSQL_PASSWORD') ?: 'pimp_pass',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'mongodb' => [
            'driver' => 'mongodb',
            'host' => getenv('MONGODB_HOST') ?: 'mongodb',
            'port' => getenv('MONGODB_PORT') ?: 27017,
            'database' => getenv('MONGO_DATABASE') ?: 'pimp_db',
            'username' => getenv('MONGO_ROOT_USERNAME') ?: 'pimp_root',
            'password' => getenv('MONGO_ROOT_PASSWORD') ?: 'pimp_root_pass',
            'options' => [
                'appname' => 'PIMP',
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => getenv('REDIS_HOST') ?: 'redis',
            'port' => getenv('REDIS_PORT') ?: 6379,
            'password' => getenv('REDIS_PASSWORD') ?: null,
            'database' => getenv('REDIS_DATABASE') ?: 0,
            'prefix' => getenv('REDIS_PREFIX') ?: 'pimp:',
            'persistent' => false,
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => getenv('SQLITE_DATABASE') ?: __DIR__ . '/../storage/database.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
    ],

    // Auto-detect settings based on environment
    'auto_detect' => [
        'enabled' => true,
        
        // Services to check
        'services' => [
            'mysql' => [
                'docker_host' => 'mysql',
                'native_hosts' => ['127.0.0.1', 'localhost', 'host.docker.internal'],
                'port' => 3306,
            ],
            'mongodb' => [
                'docker_host' => 'mongodb',
                'native_hosts' => ['127.0.0.1', 'localhost', 'host.docker.internal'],
                'port' => 27017,
            ],
            'redis' => [
                'docker_host' => 'redis',
                'native_hosts' => ['127.0.0.1', 'localhost', 'host.docker.internal'],
                'port' => 6379,
            ],
        ],
    ],
];