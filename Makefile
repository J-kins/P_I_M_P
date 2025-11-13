# Makefile for P.I.M.P Project
# Provides convenient shortcuts for common operations

.PHONY: help setup start stop restart logs shell test clean backup restore

# Default target
.DEFAULT_GOAL := help

# Colors
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[1;33m
NC := \033[0m

## help: Show this help message
help:
	@echo "$(BLUE)P.I.M.P - Available Commands$(NC)"
	@echo ""
	@grep -E '^## ' $(MAKEFILE_LIST) | sed 's/^## /  $(GREEN)/' | sed 's/:/ $(NC)- /'
	@echo ""

## setup: Interactive setup wizard
setup:
	@chmod +x scripts/quick-setup.sh
	@./scripts/quick-setup.sh

## install: Install PHP and Node dependencies
install:
	@echo "$(BLUE)Installing dependencies...$(NC)"
	@docker-compose run --rm app composer install
	@docker-compose run --rm node npm install

## start: Start all services
start:
	@echo "$(GREEN)Starting services...$(NC)"
	@docker-compose up -d
	@$(MAKE) ps

## start-full: Start all services (full profile)
start-full:
	@echo "$(GREEN)Starting all services...$(NC)"
	@COMPOSE_PROFILES=full docker-compose up -d
	@$(MAKE) ps

## start-app: Start only app and webserver
start-app:
	@echo "$(GREEN)Starting app services...$(NC)"
	@COMPOSE_PROFILES=app-only docker-compose up -d
	@$(MAKE) ps

## start-db: Start only database services
start-db:
	@echo "$(GREEN)Starting database services...$(NC)"
	@COMPOSE_PROFILES=db-only docker-compose up -d
	@$(MAKE) ps

## stop: Stop all services
stop:
	@echo "$(YELLOW)Stopping services...$(NC)"
	@docker-compose down

## restart: Restart all services
restart:
	@$(MAKE) stop
	@$(MAKE) start

## ps: Show running containers
ps:
	@docker-compose ps

## logs: Show logs from all services
logs:
	@docker-compose logs -f

## logs-app: Show app logs
logs-app:
	@docker-compose logs -f app

## logs-web: Show webserver logs
logs-web:
	@docker-compose logs -f webserver

## logs-mysql: Show MySQL logs
logs-mysql:
	@docker-compose logs -f mysql

## shell: Open shell in app container
shell:
	@docker-compose exec app bash

## shell-mysql: Open MySQL shell
shell-mysql:
	@docker-compose exec mysql mysql -u $(MYSQL_USER) -p$(MYSQL_PASSWORD) $(MYSQL_DATABASE)

## shell-mongo: Open MongoDB shell
shell-mongo:
	@docker-compose exec mongodb mongosh -u $(MONGO_ROOT_USERNAME) -p $(MONGO_ROOT_PASSWORD)

## shell-redis: Open Redis CLI
shell-redis:
	@docker-compose exec redis redis-cli -a $(REDIS_PASSWORD)

## test: Run connection tests
test:
	@echo "$(BLUE)Testing database connections...$(NC)"
	@docker-compose exec -T app php scripts/test-connections.php

## test-all: Run all tests
test-all:
	@docker-compose exec -T app vendor/bin/phpunit

## migrate: Run database migrations
migrate:
	@echo "$(BLUE)Running migrations...$(NC)"
	@docker-compose exec -T app php scripts/migrate.php

## seed: Run database seeds
seed:
	@echo "$(BLUE)Running database seeds...$(NC)"
	@docker-compose exec -T app php scripts/seed.php

## health: Check service health
health:
	@chmod +x scripts/docker-manager.sh
	@./scripts/docker-manager.sh health

## detect: Detect available services
detect:
	@chmod +x scripts/docker-manager.sh
	@./scripts/docker-manager.sh detect

## backup: Create complete backup
backup:
	@echo "$(BLUE)Creating backup...$(NC)"
	@chmod +x scripts/docker-manager.sh
	@./scripts/docker-manager.sh backup

## restore: Restore from backup (usage: make restore FILE=path/to/backup.tar.gz)
restore:
	@if [ -z "$(FILE)" ]; then \
		echo "$(YELLOW)Usage: make restore FILE=path/to/backup.tar.gz$(NC)"; \
		exit 1; \
	fi
	@chmod +x scripts/docker-manager.sh
	@./scripts/docker-manager.sh restore $(FILE)

## export: Export containers as tar files
export:
	@echo "$(BLUE)Exporting containers...$(NC)"
	@chmod +x scripts/docker-manager.sh
	@./scripts/docker-manager.sh export-containers

## save-images: Save Docker images as tar files
save-images:
	@echo "$(BLUE)Saving Docker images...$(NC)"
	@chmod +x scripts/docker-manager.sh
	@./scripts/docker-manager.sh save-images

## load-images: Load Docker images from tar files
load-images:
	@echo "$(BLUE)Loading Docker images...$(NC)"
	@chmod +x scripts/docker-manager.sh
	@./scripts/docker-manager.sh load-images

## switch-docker: Switch to full Docker mode
switch-docker:
	@chmod +x scripts/docker-manager.sh
	@./scripts/docker-manager.sh switch-env docker
	@$(MAKE) restart

## switch-native: Switch to native services mode
switch-native:
	@chmod +x scripts/docker-manager.sh
	@./scripts/docker-manager.sh switch-env native
	@$(MAKE) stop

## switch-hybrid: Switch to hybrid mode
switch-hybrid:
	@chmod +x scripts/docker-manager.sh
	@./scripts/docker-manager.sh switch-env hybrid
	@$(MAKE) restart

## build: Build Docker images
build:
	@echo "$(BLUE)Building Docker images...$(NC)"
	@docker-compose build

## rebuild: Rebuild Docker images from scratch
rebuild:
	@echo "$(BLUE)Rebuilding Docker images...$(NC)"
	@docker-compose build --no-cache
	@$(MAKE) restart

## clean: Remove all containers and volumes
clean:
	@echo "$(YELLOW)Cleaning up...$(NC)"
	@docker-compose down -v

## clean-all: Remove containers, volumes, and images
clean-all:
	@echo "$(YELLOW)Cleaning everything...$(NC)"
	@docker-compose down -v --rmi all

## update: Update dependencies
update:
	@echo "$(BLUE)Updating dependencies...$(NC)"
	@docker-compose exec -T app composer update
	@docker-compose run --rm node npm update

## fix-permissions: Fix file permissions
fix-permissions:
	@echo "$(BLUE)Fixing permissions...$(NC)"
	@sudo chown -R $(USER):$(USER) .
	@chmod -R 755 storage/
	@chmod -R 755 bootstrap/cache/

## check-ports: Check for port conflicts
check-ports:
	@chmod +x scripts/check_ports.sh
	@./scripts/check_ports.sh

## status: Show detailed status information
status:
	@echo "$(BLUE)=== Docker Status ===$(NC)"
	@docker-compose ps
	@echo ""
	@echo "$(BLUE)=== Service Health ===$(NC)"
	@$(MAKE) health
	@echo ""
	@echo "$(BLUE)=== Disk Usage ===$(NC)"
	@docker system df
	@echo ""
	@echo "$(BLUE)=== Network Info ===$(NC)"
	@docker network ls | grep pimp

## composer: Run composer command (usage: make composer CMD="require package/name")
composer:
	@docker-compose exec -T app composer $(CMD)

## npm: Run npm command (usage: make npm CMD="install package")
npm:
	@docker-compose run --rm node npm $(CMD)

## php: Run PHP command (usage: make php CMD="-v")
php:
	@docker-compose exec -T app php $(CMD)

## artisan: Run artisan command (usage: make artisan CMD="migrate")
artisan:
	@docker-compose exec -T app php artisan $(CMD)

## dump-autoload: Regenerate autoload files
dump-autoload:
	@docker-compose exec -T app composer dump-autoload

## watch: Watch logs continuously
watch:
	@docker-compose logs -f --tail=50

## top: Show container resource usage
top:
	@docker stats --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}"

## inspect: Inspect service configuration
inspect:
	@docker-compose config

## prune: Clean up unused Docker resources
prune:
	@docker system prune -f

## version: Show version information
version:
	@echo "Docker: $(shell docker --version)"
	@echo "Docker Compose: $(shell docker-compose --version)"
	@echo "PHP: $(shell docker-compose exec -T app php -v 2>/dev/null | head -n1 || echo 'Not running')"