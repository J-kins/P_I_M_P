#!/bin/bash
# scripts/check_ports.sh

check_port() {
    local port=$1
    local service=$2
    
    # Check if port is in use by another container
    if docker ps --format "table {{.Names}}\t{{.Ports}}" | grep -q ":${port}->"; then
        echo "❌ Port ${port} is already in use by another Docker container"
        return 1
    fi
    
    # Check if port is in use by host system
    if command -v netstat >/dev/null 2>&1; then
        if netstat -tuln | grep -q ":${port} "; then
            echo "❌ Port ${port} is already in use by host system"
            return 1
        fi
    fi
    
    # Check using ss (alternative to netstat)
    if command -v ss >/dev/null 2>&1; then
        if ss -tuln | grep -q ":${port} "; then
            echo "❌ Port ${port} is already in use by host system"
            return 1
        fi
    fi
    
    # Check using lsof
    if command -v lsof >/dev/null 2>&1; then
        if lsof -i :${port} >/dev/null 2>&1; then
            echo "❌ Port ${port} is already in use by host system"
            return 1
        fi
    fi
    
    echo "✅ Port ${port} is available for ${service}"
    return 0
}

check_container_conflict() {
    local container_name=$1
    local service=$2
    
    if docker ps -a --format "table {{.Names}}" | grep -q "^${container_name}$"; then
        echo "❌ Container '${container_name}' already exists for ${service}"
        return 1
    fi
    
    echo "✅ Container name '${container_name}' is available for ${service}"
    return 0
}

# Check all required ports
echo "Checking port availability..."

PORTS=(
    "${WEB_PORT:-80}:webserver"
    "${WEB_SSL_PORT:-443}:webserver_ssl"
    "${MYSQL_PORT:-3306}:mysql"
    "${MONGODB_PORT:-27017}:mongodb"
    "${REDIS_PORT:-6379}:redis"
)

CONTAINERS=(
    "${PIMP_APP_NAME:-pimp}_app:app"
    "${PIMP_APP_NAME:-pimp}_webserver:webserver"
    "${PIMP_APP_NAME:-pimp}_mysql:mysql"
    "${PIMP_APP_NAME:-pimp}_mongodb:mongodb"
    "${PIMP_APP_NAME:-pimp}_redis:redis"
    "${PIMP_APP_NAME:-pimp}_node:node"
)

all_checks_passed=true

for port_spec in "${PORTS[@]}"; do
    IFS=':' read -r port service <<< "$port_spec"
    if ! check_port "$port" "$service"; then
        all_checks_passed=false
    fi
done

for container_spec in "${CONTAINERS[@]}"; do
    IFS=':' read -r container service <<< "$container_spec"
    if ! check_container_conflict "$container" "$service"; then
        all_checks_passed=false
    fi
done

if [ "$all_checks_passed" = true ]; then
    echo "🎉 All checks passed! You can start the containers."
    exit 0
else
    echo "💥 Some checks failed. Please resolve conflicts before starting."
    exit 1
fi