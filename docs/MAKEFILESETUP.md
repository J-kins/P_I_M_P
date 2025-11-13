# P.I.M.P - Quick Usage Summary

## 🚀 Getting Started (Choose One)

### Option 1: Interactive Setup (Easiest)
```bash
chmod +x scripts/*.sh
./scripts/quick-setup.sh
```

### Option 2: Using Make Commands
```bash
make setup
make start
make test
```

### Option 3: Manual Docker
```bash
cp .env.docker-full .env
docker-compose up -d
docker-compose exec app php scripts/test-connections.php
```

---

## 📋 Common Scenarios

### Scenario 1: I Already Have MySQL Installed

```bash
# Switch to hybrid mode (Docker app + Native MySQL)
make switch-hybrid

# Edit .env to use native MySQL
nano .env
# Change: MYSQL_HOST=host.docker.internal

# Start only app services
make start-app
```

### Scenario 2: Everything in Docker

```bash
# Use full Docker mode
make switch-docker
make start-full
```

### Scenario 3: All Native (No Docker for Databases)

```bash
# Switch to native mode
make switch-native

# No Docker services needed - just use your host services
php scripts/test-connections.php
```

---

## 🔄 Switching Between Modes

```bash
# Full Docker (All services in containers)
make switch-docker && make start-full

# Native (Use host services)
make switch-native

# Hybrid (Mix of both)
make switch-hybrid && make start-app
```

---

## 📦 Transferring to Another Machine

### Complete Transfer (Code + Data + Images)

```bash
# On source machine
make backup          # Backup data
make save-images     # Save Docker images
make export          # Export containers (optional)

# Copy these to destination:
# - Entire project folder
# - docker-backups/*.tar.gz
# - docker-exports/*.tar

# On destination machine
make load-images     # Load images
make restore FILE=docker-backups/backup_file.tar.gz
make start
```

### Code Only Transfer (Rebuild on Destination)

```bash
# Just copy project folder
rsync -avz --exclude 'vendor' --exclude 'node_modules' ./ user@remote:/path/

# On destination
make install
make build
make start
```

---

## 🧪 Testing

```bash
# Test all database connections
make test

# Check service health
make health

# Detect available services
make detect

# View logs
make logs
make logs-mysql
make logs-app
```

---

## 🔧 Management Commands

### Starting/Stopping

```bash
make start           # Start services
make stop            # Stop services
make restart         # Restart services
make ps              # Show status
```

### Database Operations

```bash
make migrate         # Run migrations
make seed            # Run seeds
make shell-mysql     # Open MySQL shell
make shell-mongo     # Open MongoDB shell
make shell-redis     # Open Redis CLI
```

### Backup/Restore

```bash
make backup          # Create complete backup
make restore FILE=<path>  # Restore from backup
```

### Maintenance

```bash
make clean           # Remove containers & volumes
make rebuild         # Rebuild from scratch
make fix-permissions # Fix file permissions
make update          # Update dependencies
```

---

## 🐛 Troubleshooting

### Port Already in Use

```bash
# Check ports
make check-ports

# Edit .env to use different ports
nano .env
# Change: WEB_PORT=8080, MYSQL_PORT=3307, etc.
```

### Cannot Connect to Database

```bash
# Test connections
make test

# Check if services are running
make ps
make health

# View logs
make logs-mysql

# Restart services
make restart
```

### Permission Issues

```bash
make fix-permissions
```

### Missing PDO Driver

```bash
# Check installed extensions
docker-compose exec app php -m | grep pdo

# Rebuild containers
make rebuild
```

### Native MySQL Not Accessible from Docker

In `.env`, use:
```env
MYSQL_HOST=host.docker.internal  # Not localhost or 127.0.0.1
```

---

## 📝 Environment Files

| File | Purpose |
|------|---------|
| `.env.docker-full` | All services in Docker |
| `.env.native` | All services native |
| `.env.hybrid` | Mix of Docker and native |
| `.env` | Active configuration |

---

## 🔌 Accessing Services

### From Host Machine

```bash
# Web
http://localhost:8080

# MySQL
mysql -h 127.0.0.1 -P 3306 -u pimp_user -p

# MongoDB
mongosh mongodb://127.0.0.1:27017

# Redis
redis-cli -h 127.0.0.1 -p 6379
```

### From Docker Container

```bash
# App shell
make shell

# Inside container
php scripts/test-connections.php
```

---

## 📊 Monitoring

```bash
# View logs
make logs

# Watch logs continuously
make watch

# Resource usage
make top

# Detailed status
make status
```

---

## 🎯 Quick Reference

| Command | What It Does |
|---------|--------------|
| `make setup` | Interactive setup wizard |
| `make start` | Start services |
| `make stop` | Stop services |
| `make test` | Test connections |
| `make health` | Check service health |
| `make logs` | View logs |
| `make shell` | Open container shell |
| `make backup` | Create backup |
| `make switch-docker` | Use all Docker |
| `make switch-native` | Use all native |
| `make switch-hybrid` | Use mixed mode |

---

## 🆘 Need Help?

```bash
# Show all available commands
make help

# Read detailed documentation
cat docs/DOCKER.md

# Check service detection
make detect

# View service health
make health
```

---

## ⚡ Pro Tips

1. **Use Makefile**: All commands are available via `make <command>`
2. **Auto-Detection**: System automatically detects Docker vs native services
3. **Portable Backups**: Use `make backup` before transferring projects
4. **Hybrid Mode**: Best for development when you already have MySQL/etc installed
5. **Check Logs**: Always check logs first when debugging: `make logs`

---

## 🔐 Security Notes

Before production:
1. Change all passwords in `.env`
2. Remove exposed ports or bind to localhost only
3. Use proper secrets management
4. Keep images updated: `docker-compose pull`
5. Enable SSL/TLS for web server