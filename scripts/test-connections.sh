<?php
/**
 * scripts/test-connections.php
 * Test all database connections and display results
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PIMP\Services\DatabaseFactory;
use PIMP\Services\ServiceDetector;

// ANSI color codes
class Colors {
    const RESET = "\033[0m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";
}

function printHeader($text) {
    echo "\n" . Colors::BLUE . Colors::BOLD . "═══════════════════════════════════════════════════════════" . Colors::RESET . "\n";
    echo Colors::CYAN . $text . Colors::RESET . "\n";
    echo Colors::BLUE . "═══════════════════════════════════════════════════════════" . Colors::RESET . "\n\n";
}

function printSuccess($text) {
    echo Colors::GREEN . "✓ " . $text . Colors::RESET . "\n";
}

function printError($text) {
    echo Colors::RED . "✗ " . $text . Colors::RESET . "\n";
}

function printWarning($text) {
    echo Colors::YELLOW . "⚠ " . $text . Colors::RESET . "\n";
}

function printInfo($text) {
    echo Colors::BLUE . "ℹ " . $text . Colors::RESET . "\n";
}

// Display banner
echo "\n";
echo Colors::CYAN . "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                                                           ║\n";
echo "║   P.I.M.P - Database Connection Tester                   ║\n";
echo "║                                                           ║\n";
echo "╚═══════════════════════════════════════════════════════════╝" . Colors::RESET . "\n";

// System Information
printHeader("System Information");

$systemInfo = ServiceDetector::getSystemInfo();

echo "PHP Version:      " . $systemInfo['php_version'] . "\n";
echo "Operating System: " . $systemInfo['os'] . "\n";
echo "Running in:       " . ($systemInfo['is_docker'] ? Colors::BLUE . "Docker Container" : Colors::YELLOW . "Native Environment") . Colors::RESET . "\n";
echo "Service Mode:     " . strtoupper($systemInfo['service_mode']) . "\n";
echo "PDO Drivers:      " . implode(", ", $systemInfo['pdo_drivers']) . "\n";

// Check extensions
printHeader("PHP Extensions Check");

$requiredExtensions = ['pdo', 'pdo_mysql', 'pdo_sqlite', 'mongodb', 'redis', 'mbstring', 'json'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        printSuccess("$ext: loaded");
    } else {
        printError("$ext: NOT loaded");
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    printWarning("Missing extensions: " . implode(", ", $missingExtensions));
    echo "\n";
}

// Service Detection
printHeader("Service Detection");

$detectionResults = ServiceDetector::testAllServices();

foreach ($detectionResults as $service => $result) {
    $serviceName = strtoupper($service);
    
    if ($result['accessible']) {
        $typeColor = $result['type'] === 'docker' ? Colors::BLUE : Colors::YELLOW;
        printSuccess("$serviceName: Accessible at {$result['host']}:{$result['port']} " . 
                    $typeColor . "({$result['type']})" . Colors::RESET);
    } else {
        printError("$serviceName: Not accessible");
    }
}

// Database Connection Tests
printHeader("Database Connection Tests");

$databases = ['mysql', 'mongodb', 'redis', 'sqlite'];
$connectionResults = [];

foreach ($databases as $dbType) {
    echo "\n" . Colors::BOLD . "Testing " . strtoupper($dbType) . "..." . Colors::RESET . "\n";
    
    try {
        $db = DatabaseFactory::create($dbType);
        $config = $db->getConfig();
        
        // Connection info
        echo "  Host:     " . ($config['host'] ?? 'N/A') . "\n";
        echo "  Port:     " . ($config['port'] ?? 'N/A') . "\n";
        echo "  Database: " . ($config['database'] ?? 'N/A') . "\n";
        echo "  Type:     " . ($config['_detected_type'] ?? 'manual') . "\n";
        
        // Try to connect
        $startTime = microtime(true);
        $connected = $db->isConnected();
        $endTime = microtime(true);
        $latency = round(($endTime - $startTime) * 1000, 2);
        
        if ($connected) {
            printSuccess("Connected successfully (${latency}ms)");
            
            // Additional tests based on database type
            switch ($dbType) {
                case 'mysql':
                    try {
                        $version = $db->fetchColumn('SELECT VERSION()');
                        echo "  Version:  $version\n";
                        
                        // Test query
                        $db->query('SELECT 1');
                        printSuccess("Query test passed");
                    } catch (Exception $e) {
                        printWarning("Query test failed: " . $e->getMessage());
                    }
                    break;
                    
                case 'mongodb':
                    try {
                        $collections = $db->listCollections();
                        $count = count($collections);
                        echo "  Collections: $count\n";
                        printSuccess("Collection listing passed");
                    } catch (Exception $e) {
                        printWarning("Collection test failed: " . $e->getMessage());
                    }
                    break;
                    
                case 'redis':
                    try {
                        $info = $db->info();
                        echo "  Version:  " . ($info['redis_version'] ?? 'Unknown') . "\n";
                        
                        // Test set/get
                        $testKey = 'pimp:test:' . time();
                        $db->set($testKey, 'test_value', 10);
                        $value = $db->get($testKey);
                        $db->delete($testKey);
                        
                        if ($value === 'test_value') {
                            printSuccess("Read/Write test passed");
                        } else {
                            printWarning("Read/Write test failed");
                        }
                    } catch (Exception $e) {
                        printWarning("Redis test failed: " . $e->getMessage());
                    }
                    break;
                    
                case 'sqlite':
                    try {
                        $version = $db->fetchColumn('SELECT sqlite_version()');
                        echo "  Version:  $version\n";
                        
                        // Test query
                        $db->query('SELECT 1');
                        printSuccess("Query test passed");
                    } catch (Exception $e) {
                        printWarning("Query test failed: " . $e->getMessage());
                    }
                    break;
            }
            
            $connectionResults[$dbType] = true;
        } else {
            printError("Connection failed");
            $connectionResults[$dbType] = false;
        }
    } catch (Exception $e) {
        printError("Error: " . $e->getMessage());
        echo "  Details: " . get_class($e) . "\n";
        echo "  Trace:   " . $e->getFile() . ":" . $e->getLine() . "\n";
        $connectionResults[$dbType] = false;
    }
}

// Summary
printHeader("Summary");

$totalTests = count($connectionResults);
$passedTests = count(array_filter($connectionResults));
$failedTests = $totalTests - $passedTests;
$successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;

echo "Total Tests:   $totalTests\n";
echo "Passed:        " . Colors::GREEN . "$passedTests ✓" . Colors::RESET . "\n";
echo "Failed:        " . ($failedTests > 0 ? Colors::RED : Colors::GREEN) . "$failedTests" . Colors::RESET . "\n";
echo "Success Rate:  " . ($successRate == 100 ? Colors::GREEN : ($successRate >= 50 ? Colors::YELLOW : Colors::RED)) . "$successRate%" . Colors::RESET . "\n";

// Recommendations
if ($failedTests > 0) {
    printHeader("Recommendations");
    
    foreach ($connectionResults as $dbType => $passed) {
        if (!$passed) {
            echo "\n" . Colors::YELLOW . strtoupper($dbType) . " Failed:" . Colors::RESET . "\n";
            
            switch ($dbType) {
                case 'mysql':
                    echo "  • Check if MySQL is running\n";
                    echo "  • Verify credentials in .env file\n";
                    echo "  • Test manually: mysql -h<host> -u<user> -p\n";
                    echo "  • Docker: docker-compose ps mysql\n";
                    echo "  • Native: sudo systemctl status mysql\n";
                    break;
                    
                case 'mongodb':
                    echo "  • Check if MongoDB is running\n";
                    echo "  • Verify connection string in .env file\n";
                    echo "  • Test manually: mongosh mongodb://<host>:<port>\n";
                    echo "  • Docker: docker-compose ps mongodb\n";
                    echo "  • Native: sudo systemctl status mongod\n";
                    break;
                    
                case 'redis':
                    echo "  • Check if Redis is running\n";
                    echo "  • Verify password in .env file\n";
                    echo "  • Test manually: redis-cli -h <host> -p <port> ping\n";
                    echo "  • Docker: docker-compose ps redis\n";
                    echo "  • Native: sudo systemctl status redis\n";
                    break;
                    
                case 'sqlite':
                    echo "  • Check if storage directory is writable\n";
                    echo "  • Verify database path in .env file\n";
                    echo "  • Create directory: mkdir -p storage\n";
                    echo "  • Set permissions: chmod 755 storage\n";
                    break;
            }
        }
    }
    
    echo "\n" . Colors::CYAN . "For more help, run:" . Colors::RESET . "\n";
    echo "  ./scripts/docker-manager.sh detect\n";
    echo "  ./scripts/docker-manager.sh health\n";
}

// Exit code based on results
exit($failedTests > 0 ? 1 : 0);