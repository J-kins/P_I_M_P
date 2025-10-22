<?php
/**
 * service/ServiceDetector.php
 * Automatically detect whether services are running in Docker or natively
 */

namespace PIMP\Services;

class ServiceDetector
{
    /**
     * Check if running inside Docker container
     */
    public static function isDocker(): bool
    {
        return file_exists('/.dockerenv') || 
               (file_exists('/proc/1/cgroup') && 
                strpos(file_get_contents('/proc/1/cgroup'), 'docker') !== false);
    }

    /**
     * Check if a service is accessible
     */
    public static function isServiceAccessible(string $host, int $port, int $timeout = 2): bool
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
        
        if ($connection) {
            fclose($connection);
            return true;
        }
        
        return false;
    }

    /**
     * Auto-detect the best host for a service
     */
    public static function detectServiceHost(string $service): array
    {
        $config = require __DIR__ . '/../config/database.php';
        
        if (!isset($config['auto_detect']['services'][$service])) {
            throw new \InvalidArgumentException("Unknown service: {$service}");
        }

        $serviceConfig = $config['auto_detect']['services'][$service];
        $port = $serviceConfig['port'];
        
        // Try Docker host first if we're in Docker
        if (self::isDocker()) {
            $dockerHost = $serviceConfig['docker_host'];
            if (self::isServiceAccessible($dockerHost, $port)) {
                return [
                    'host' => $dockerHost,
                    'port' => $port,
                    'type' => 'docker',
                    'accessible' => true,
                ];
            }
        }
        
        // Try native hosts
        foreach ($serviceConfig['native_hosts'] as $nativeHost) {
            if (self::isServiceAccessible($nativeHost, $port)) {
                return [
                    'host' => $nativeHost,
                    'port' => $port,
                    'type' => 'native',
                    'accessible' => true,
                ];
            }
        }
        
        // Return default with accessible = false
        return [
            'host' => $serviceConfig['docker_host'],
            'port' => $port,
            'type' => 'unknown',
            'accessible' => false,
        ];
    }

    /**
     * Get optimized database configuration
     */
    public static function getOptimizedConfig(string $service): array
    {
        $baseConfig = require __DIR__ . '/../config/database.php';
        $connectionConfig = $baseConfig['connections'][$service] ?? [];
        
        if (!$connectionConfig) {
            throw new \InvalidArgumentException("Unknown service: {$service}");
        }

        // Auto-detect if enabled
        if ($baseConfig['auto_detect']['enabled'] ?? false) {
            $detected = self::detectServiceHost($service);
            
            if ($detected['accessible']) {
                $connectionConfig['host'] = $detected['host'];
                $connectionConfig['port'] = $detected['port'];
                $connectionConfig['detected_type'] = $detected['type'];
            }
        }
        
        return $connectionConfig;
    }

    /**
     * Test all configured services
     */
    public static function testAllServices(): array
    {
        $config = require __DIR__ . '/../config/database.php';
        $results = [];
        
        foreach ($config['auto_detect']['services'] as $service => $serviceConfig) {
            $results[$service] = self::detectServiceHost($service);
        }
        
        return $results;
    }

    /**
     * Get system info
     */
    public static function getSystemInfo(): array
    {
        return [
            'is_docker' => self::isDocker(),
            'php_version' => phpversion(),
            'os' => PHP_OS,
            'loaded_extensions' => get_loaded_extensions(),
            'pdo_drivers' => \PDO::getAvailableDrivers(),
            'service_mode' => getenv('SERVICE_MODE') ?: 'auto',
        ];
    }
}