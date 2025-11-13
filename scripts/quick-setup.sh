#!/bin/bash
# scripts/quick-setup.sh
# Interactive setup for PIMP project

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

clear

cat << "EOF"
╔═══════════════════════════════════════════════════════════╗
║                                                           ║
║   ██████╗ ██╗███╗   ███╗██████╗                          ║
║   ██╔══██╗██║████╗ ████║██╔══██╗                         ║
║   ██████╔╝██║██╔████╔██║██████╔╝                         ║
║   ██╔═══╝ ██║██║╚██╔╝██║██╔═══╝                          ║
║   ██║     ██║██║ ╚═╝ ██║██║                              ║
║   ╚═╝     ╚═╝╚═╝     ╚═╝╚═╝                              ║
║                                                           ║
║   P.I.M.P - Quick Setup Wizard                           ║
║                                                           ║
╚═══════════════════════════════════════════════════════════╝
EOF

echo ""

# Detect OS
detect_os() {
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        echo "linux"
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        echo "macos"
    elif [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "cygwin" ]]; then
        echo "windows"
    else
        echo "unknown"
    fi
}

OS=$(detect_os)
echo -e "${CYAN}Detected OS: ${OS}${NC}\n"

# Check for Docker
check_docker() {
    if command -v docker &> /dev/null && docker info &> /dev/null; then
        return 0
    else
        return 1
    fi
}

# Check for native services
check_native_mysql() {
    if command -v mysql &> /dev/null || [ -f "/usr/local/bin/mysql" ] || [ -f "/usr/bin/mysql" ]; then
        if nc -z localhost 3306 2>/dev/null || (command -v mysql &> /dev/null && mysql -e "SELECT 1" &> /dev/null); then
            return 0
        fi
    fi
    return 1
}

check_native_mongodb() {
    if command -v mongod &> /dev/null || nc -z localhost 27017 2>/dev/null; then
        return 0
    fi
    return 1
}

check_native_redis() {
    if command -v redis-cli &> /dev/null || nc -z localhost 6379 2>/dev/null; then
        return 0
    fi
    return 1
}

check_native_php() {
    if command -v php &> /dev/null; then
        return 0
    fi
    return 1
}

# Detect available services
echo -e "${BLUE}🔍 Detecting available services...${NC}\n"

DOCKER_AVAILABLE=$(check_docker && echo "yes" || echo "no")
NATIVE_MYSQL=$(check_native_mysql && echo "yes" || echo "no")
NATIVE_MONGODB=$(check_native_mongodb && echo "yes" || echo "no")
NATIVE_REDIS=$(check_native_redis && echo "yes" || echo "no")
NATIVE_PHP=$(check_native_php && echo "yes" || echo "no")

echo "Docker:        $DOCKER_AVAILABLE"
echo "Native MySQL:  $NATIVE_MYSQL"
echo "Native MongoDB: $NATIVE_MONGODB"
echo "Native Redis:   $NATIVE_REDIS"
echo "Native PHP:     $NATIVE_PHP"
echo ""

# Interactive mode selection
echo -e "${YELLOW}Select your deployment mode:${NC}\n"
echo "1) Full Docker (All services in containers)"
echo "2) Native Services (Use installed services on host)"
echo "3) Hybrid (Mix of Docker and native)"
echo "4) Custom Configuration"
echo ""

read -p "Enter your choice (1-4): " choice

case $choice in
    1)
        MODE="docker"
        PROFILE="full"
        echo -e "\n${GREEN}✓ Full Docker mode selected${NC}"
        ;;
    2)
        MODE="native"
        PROFILE="none"
        echo -e "\n${GREEN}✓ Native services mode selected${NC}"
        ;;
    3)
        MODE="hybrid"
        echo -e "\n${YELLOW}Configuring hybrid mode...${NC}\n"
        
        # MySQL
        if [ "$NATIVE_MYSQL" = "yes" ]; then
            read -p "Use native MySQL? (Y/n): " use_native_mysql
            MYSQL_MODE=$([ "$use_native_mysql" = "n" ] && echo "docker" || echo "native")
        else
            MYSQL_MODE="docker"
        fi
        
        # MongoDB
        if [ "$NATIVE_MONGODB" = "yes" ]; then
            read -p "Use native MongoDB? (Y/n): " use_native_mongodb
            MONGODB_MODE=$([ "$use_native_mongodb" = "n" ] && echo "docker" || echo "native")
        else
            MONGODB_MODE="docker"
        fi
        
        # Redis
        if [ "$NATIVE_REDIS" = "yes" ]; then
            read -p "Use native Redis? (Y/n): " use_native_redis
            REDIS_MODE=$([ "$use_native_redis" = "n" ] && echo "docker" || echo "native")
        else
            REDIS_MODE="docker"
        fi
        
        # Determine profile
        if [ "$MYSQL_MODE" = "native" ] && [ "$MONGODB_MODE" = "native" ] && [ "$REDIS_MODE" = "native" ]; then
            PROFILE="app-only"
        else
            PROFILE="full"
        fi
        ;;
    4)
        echo -e "\n${CYAN}Opening custom configuration...${NC}"
        ${EDITOR:-nano} .env
        exit 0
        ;;
    *)
        echo -e "${RED}Invalid choice${NC}"
        exit 1
        ;;
esac

# Generate .env file
echo -e "\n${BLUE}📝 Generating configuration...${NC}\n"

cat > .env <<EOF
# Generated by quick-setup.sh on $(date)
# Mode: $MODE

PIMP_ENV=development
PIMP_APP_NAME=pimp
TZ=UTC
SERVICE_MODE=$MODE

# Web Server
WEB_PORT=8080
WEB_SSL_PORT=8443

# MySQL Configuration
EOF

if [ "$MODE" = "docker" ] || ([ "$MODE" = "hybrid" ] && [ "$MYSQL_MODE" = "docker" ]); then
    cat >> .env <<EOF
MYSQL_HOST=mysql
MYSQL_PORT=3306
EOF
else
    cat >> .env <<EOF
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
EOF
fi

cat >> .env <<EOF
MYSQL_ROOT_PASSWORD=pimp_root_pass
MYSQL_DATABASE=pimp_db
MYSQL_USER=pimp_user
MYSQL_PASSWORD=pimp_pass

# MongoDB Configuration
EOF

if [ "$MODE" = "docker" ] || ([ "$MODE" = "hybrid" ] && [ "$MONGODB_MODE" = "docker" ]); then
    cat >> .env <<EOF
MONGODB_HOST=mongodb
MONGODB_PORT=27017
EOF
else
    cat >> .env <<EOF
MONGODB_HOST=127.0.0.1
MONGODB_PORT=27017
EOF
fi

cat >> .env <<EOF
MONGO_ROOT_USERNAME=pimp_root
MONGO_ROOT_PASSWORD=pimp_root_pass
MONGO_DATABASE=pimp_db

# Redis Configuration
EOF

if [ "$MODE" = "docker" ] || ([ "$MODE" = "hybrid" ] && [ "$REDIS_MODE" = "docker" ]); then
    cat >> .env <<EOF
REDIS_HOST=redis
REDIS_PORT=6379
EOF
else
    cat >> .env <<EOF
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
EOF
fi

cat >> .env <<EOF
REDIS_PASSWORD=pimp_redis_pass

# SQLite
SQLITE_DATABASE=./storage/database.sqlite

# Docker Compose Profile
COMPOSE_PROFILES=$PROFILE
EOF

echo -e "${GREEN}✓ Configuration saved to .env${NC}\n"

# Ask to start services
if [ "$MODE" != "native" ] && [ "$DOCKER_AVAILABLE" = "yes" ]; then
    read -p "Start Docker services now? (Y/n): " start_now
    
    if [ "$start_now" != "n" ]; then
        echo -e "\n${BLUE}🚀 Starting services...${NC}\n"
        
        if [ "$PROFILE" = "none" ]; then
            echo "Native mode - no Docker services to start"
        else
            docker-compose up -d
            echo ""
            docker-compose ps
        fi
        
        echo -e "\n${GREEN}✓ Services started successfully!${NC}"
        
        # Run detection
        echo -e "\n${BLUE}🔍 Testing database connections...${NC}\n"
        sleep 5  # Wait for services to be ready
        
        if docker-compose ps | grep -q "${PIMP_APP_NAME}_app"; then
            docker-compose exec -T app php -r "
                require_once 'vendor/autoload.php';
                \$factory = new PIMP\Services\DatabaseFactory();
                \$results = \$factory->testAll();
                foreach (\$results as \$db => \$result) {
                    echo strtoupper(\$db) . ': ';
                    echo \$result['status'] === 'connected' ? '✓ Connected' : '✗ Failed';
                    if (isset(\$result['type'])) {
                        echo ' (' . \$result['type'] . ')';
                    }
                    echo PHP_EOL;
                }
            " 2>/dev/null || echo "App container not ready yet - you can test connections later"
        fi
    fi
fi

# Show next steps
cat << EOF

${GREEN}╔═══════════════════════════════════════════════════════════╗
║                    Setup Complete! ✓                      ║
╚═══════════════════════════════════════════════════════════╝${NC}

${CYAN}Next Steps:${NC}

1. Access your application:
   ${BLUE}http://localhost:${WEB_PORT:-8080}${NC}

2. View logs:
   ${BLUE}docker-compose logs -f${NC}

3. Run migrations:
   ${BLUE}docker-compose exec app php scripts/migrate.php${NC}

4. Test database connections:
   ${BLUE}docker-compose exec app php scripts/test-connections.php${NC}

${CYAN}Useful Commands:${NC}

  • Switch environment:    ${BLUE}./scripts/docker-manager.sh switch-env <mode>${NC}
  • Stop services:         ${BLUE}docker-compose down${NC}
  • View health:          ${BLUE}./scripts/docker-manager.sh health${NC}
  • Create backup:        ${BLUE}./scripts/docker-manager.sh backup${NC}
  • Export containers:    ${BLUE}./scripts/docker-manager.sh export-containers${NC}

${YELLOW}Documentation: ${BLUE}./docs/README.md${NC}

EOF