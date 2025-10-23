# P.I.M.P Docker Setup and Management Guide

## Overview

P.I.M.P supports three deployment modes:

1. **Full Docker**: All services run in containers
2. **Native**: All services use host installations
3. **Hybrid**: Mix of Docker and native services

## Quick Start

### 1. Interactive Setup (Recommended)

```bash
chmod +x scripts/quick-setup.sh
./scripts/quick-setup.sh
```

This will:
- Detect available services (Docker/Native)
- Configure the optimal deployment mode
- Generate `.env` configuration
- Start services automatically

### 2. Manual Setup

#### Full Docker Mode

```bash
# Copy environment template
cp .env.docker-full .env

# Start all services
docker-compose --profile full up -d

# Check status
docker-compose ps
```

#### Native Mode (Use Host Services)

```bash
# Copy environment template
cp .env.native .env

# No Docker services needed - use host MySQL/MongoDB/Redis/PHP
```

#### Hybrid Mode

```bash
# Copy environment template
cp .env.hybrid .env

# Edit to specify which services to use from Docker
nano .env

# Start only needed services
docker-compose --profile app-only up -d
```

## Environment Configuration

### Full Docker (.env.docker-full)

All services run in containers:

```env
SERVICE_MODE=docker

# Services use Docker container names
MYSQL_HOST=mysql
MONGODB_HOST=mongodb
REDIS_HOST=redis

COMPOSE_PROFILES=full
```

### Native (.env.native)

All services run on host:

```env
SERVICE_MODE=native

# Services use localhost
MYSQL_HOST=127.0.0.1
MONGODB_HOST=127.0.0.1
REDIS_HOST=127.0.0.1

COMPOSE_PROFILES=app-only  # Only start app container
```

### Hybrid (.env.hybrid)

Mix of Docker and native services:

```env
SERVICE_MODE=hybrid

# Use native MySQL
MYSQL_HOST=host.docker.internal
MYSQL_PORT=3306

# Use Docker MongoDB
MONGODB_HOST=mongodb
MONGODB_PORT=27017

# Use native Redis
REDIS_HOST=host.docker.internal
REDIS_PORT=6379

COMPOSE_PROFILES=app-only  # Don't start native services
```

## Docker Compose Profiles

Control which services start:

| Profile | Services Started |
|---------|-----------------|
| `full` | app, webserver, mysql, mongodb, redis |
| `app-only` | app, webserver only |
| `db-only` | mysql, mongodb, redis only |
| `frontend` | node (for builds) |

Example:
```bash
# Start only app and webserver
COMPOSE_PROFILES=app-only docker-compose up -d

# Start only databases
COMPOSE_PROFILES=db-only docker-compose up -d
```

## Management Scripts

### Docker Manager

```bash
# Make executable
chmod +x scripts/docker-manager.sh

# Switch environment mode
./scripts/docker-manager.sh switch-env docker    # Full Docker
./scripts/docker-manager.sh switch-env native    # Native services
./scripts/docker-manager.sh switch-env hybrid    # Hybrid mode

# Start services
./scripts/docker-manager.sh start full          # All services
./scripts/docker-manager.sh start app-only      # App only

# Check health
./scripts/docker-manager.sh health

# Detect services
./scripts/docker-manager.sh detect

# Stop services
./scripts/docker-manager.sh stop
```

## Container Export/Import (Portability)

### Export Containers

Export containers for transfer to another machine:

```bash
# Export all containers as tar files
./scripts/docker-manager.sh export-containers

# Files saved to: ./docker-exports/
```

### Save Images

Save Docker images:

```bash
# Save all images
./scripts/docker-manager.sh save-images

# Files saved to: ./docker-exports/
```

### Load Images (On New Machine)

```bash
# Copy docker-exports folder to new machine

# Load images
./scripts/docker-manager.sh load-images

# Start services
docker-compose up -d
```

### Complete Backup (Volumes + Config)

Create a complete backup including volumes and configuration:

```bash
# Create backup
./scripts/docker-manager.sh backup

# Backup saved to: ./docker-backups/pimp_backup_YYYYMMDD_HHMMSS.tar.gz
```

### Restore from Backup

```bash
# Restore from backup file
./scripts/docker-manager.sh restore ./docker-backups/pimp_backup_20250101_120000.tar.gz
```

## Transfer Project to Another Machine

### Method 1: Complete Package (Recommended)

```bash
# On source machine
./scripts/docker-manager.sh backup
./scripts/docker-manager.sh save-images

# Transfer these files:
# 1. Project directory (code)
# 2. docker-backups/*.tar.gz (data)
# 3. docker-exports/*.tar (images)

# On destination machine
./scripts/docker-manager.sh load-images
./scripts/docker-manager.sh restore <backup-file>
docker-compose up -d
```

### Method 2: Code Only (Rebuild)

```bash
# Transfer project directory
rsync -avz --exclude 'node_modules' --exclude 'vendor' ./ user@remote:/path/

# On destination machine
composer install
docker-compose build
docker-compose up -d
```

## Testing Connections

Test all database connections:

```bash
# From host
docker-compose exec app php scripts/test-connections.php

# Inside container
docker-compose exec app bash
php scripts/test-connections.php
```

## Common Scenarios

### Scenario 1: Already Have MySQL Installed

```bash
# Use hybrid mode with native MySQL
cp .env.hybrid .env

# Edit .env
nano .env
# Set: MYSQL_HOST=host.docker.internal

# Start only app and other services
docker-compose --profile app-only up -d
```

### Scenario 2: XAMPP/WAMP on Windows

```bash
# Use native mode
cp .env.native .env

# Edit .env for XAMPP defaults
nano .env
# Set:
# MYSQL_HOST=127.0.0.1
# MYSQL_PORT=3306
# MYSQL_USER=root
# MYSQL_PASSWORD=

# No Docker services needed
```

### Scenario 3: Development on Mac, Production on Linux

```bash
# On Mac (Development)
cp .env.docker-full .env
docker-compose up -d

# Create portable backup
./scripts/docker-manager.sh backup
./scripts/docker-manager.sh save-images

# Transfer to Linux server
scp docker-backups/*.tar.gz user@server:/path/
scp docker-exports/*.tar user@server:/path/

# On Linux server
./scripts/docker-manager.sh load-images
./scripts/docker-manager.sh restore <backup-file>
docker-compose up -d
```

## Troubleshooting

### Port Conflicts

```bash
# Check for port conflicts
./scripts/check_ports.sh

# Change ports in .env
WEB_PORT=8080        # Instead of 80
MYSQL_PORT=3307      # Instead of 3306
```

### Cannot Connect to Native MySQL from Docker

Use `host.docker.internal` instead of `localhost` or `127.0.0.1`:

```env
MYSQL_HOST=host.docker.internal
```

### Permission Issues

```bash
# Fix storage permissions
chmod -R 755 storage/
chown -R 1000:1000 storage/

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Missing PDO Driver

```bash
# Check installed drivers
docker-compose exec app php -m | grep -i pdo

# Rebuild with correct extensions
docker-compose build --no-cache app
```

### Database Connection Fails

```bash
# Test connectivity
docker-compose exec app php scripts/test-connections.php

# Check service logs
docker-compose logs mysql
docker-compose logs mongodb
docker-compose logs redis

# Restart services
docker-compose restart mysql
```

## Advanced Usage

### Access Container Shell

```bash
# App container
docker-compose exec app bash

# MySQL container
docker-compose exec mysql bash

# Run MySQL client
docker-compose exec mysql mysql -u root -p
```

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f mysql
docker-compose logs -f app

# Last 100 lines
docker-compose logs --tail=100 app
```

### Database Operations

```bash
# Run migrations
docker-compose exec app php scripts/migrate.php

# Import SQL file
docker-compose exec -T mysql mysql -u root -p<password> pimp_db < dump.sql

# Backup database
docker-compose exec mysql mysqldump -u root -p<password> pimp_db > backup.sql
```

### Network Inspection

```bash
# List networks
docker network ls

# Inspect PIMP network
docker network inspect pimp_network

# Test connectivity between containers
docker-compose exec app ping mysql
docker-compose exec app nc -zv mysql 3306
```

## Performance Optimization

### Use Volumes for Performance

Already configured in docker-compose.yml for optimal performance.

### Resource Limits

Add to docker-compose.yml:

```yaml
services:
  mysql:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          memory: 512M
```

### Use BuildKit

```bash
export DOCKER_BUILDKIT=1
export COMPOSE_DOCKER_CLI_BUILD=1

docker-compose build
```

## Security Best Practices

1. **Change default passwords** in `.env`
2. **Use secrets** for production:
   ```bash
   docker secret create mysql_root_password password.txt
   ```
3. **Limit exposed ports** - bind to localhost:
   ```yaml
   ports:
     - "127.0.0.1:3306:3306"
   ```
4. **Use read-only volumes** where possible
5. **Keep images updated**:
   ```bash
   docker-compose pull
   docker-compose up -d
   ```

## Environment Variables Reference

| Variable | Description | Default |
|----------|-------------|---------|
| `PIMP_ENV` | Environment (development/production) | development |
| `SERVICE_MODE` | Service mode (docker/native/hybrid) | docker |
| `MYSQL_HOST` | MySQL host | mysql |
| `MYSQL_PORT` | MySQL port | 3306 |
| `MONGODB_HOST` | MongoDB host | mongodb |
| `MONGODB_PORT` | MongoDB port | 27017 |
| `REDIS_HOST` | Redis host | redis |
| `REDIS_PORT` | Redis port | 6379 |
| `COMPOSE_PROFILES` | Active profile | full |

## Support

For issues:
1. Check logs: `docker-compose logs`
2. Test connections: `php scripts/test-connections.php`
3. Verify health: `./scripts/docker-manager.sh health`
4. Review configuration: `.env` file