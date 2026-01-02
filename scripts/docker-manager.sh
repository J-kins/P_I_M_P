#!/bin/bash
# scripts/docker-manager.sh
# Comprehensive Docker container management

set -e

PROJECT_NAME="${PIMP_APP_NAME:-pimp}"
EXPORT_DIR="./docker-exports"
BACKUP_DIR="./docker-backups"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
print_header() {
    echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║  $1${NC}"
    echo -e "${BLUE}╚════════════════════════════════════════╝${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Load environment
load_env() {
    local env_file="${1:-.env}"
    if [ -f "$env_file" ]; then
        export $(cat "$env_file" | grep -v '^#' | xargs)
        print_success "Loaded environment from $env_file"
    else
        print_warning "Environment file $env_file not found"
    fi
}

# Switch environment configuration
switch_env() {
    local mode="$1"
    local env_file=""
    
    case "$mode" in
        docker|full)
            env_file=".env.docker-full"
            ;;
        native)
            env_file=".env.native"
            ;;
        hybrid)
            env_file=".env.hybrid"
            ;;
        *)
            print_error "Unknown mode: $mode"
            echo "Available modes: docker, native, hybrid"
            exit 1
            ;;
    esac
    
    if [ -f "$env_file" ]; then
        cp "$env_file" .env
        print_success "Switched to $mode mode"
        print_info "Active configuration: $env_file"
    else
        print_error "Configuration file not found: $env_file"
        exit 1
    fi
}

# Start services based on profile
start_services() {
    local profile="${1:-full}"
    
    print_header "Starting services with profile: $profile"
    
    COMPOSE_PROFILES="$profile" docker-compose up -d
    
    print_success "Services started"
    docker-compose ps
}

# Stop services
stop_services() {
    print_header "Stopping all services"
    docker-compose down
    print_success "All services stopped"
}

# Export containers as tar files
export_containers() {
    print_header "Exporting Docker containers"
    
    mkdir -p "$EXPORT_DIR"
    
    # Get list of containers
    local containers=$(docker-compose ps -q)
    
    if [ -z "$containers" ]; then
        print_warning "No running containers found"
        return
    fi
    
    for container_id in $containers; do
        local container_name=$(docker inspect --format='{{.Name}}' "$container_id" | sed 's/\///')
        local export_file="$EXPORT_DIR/${container_name}_$(date +%Y%m%d_%H%M%S).tar"
        
        print_info "Exporting $container_name..."
        docker export "$container_id" -o "$export_file"
        
        if [ -f "$export_file" ]; then
            local size=$(du -h "$export_file" | cut -f1)
            print_success "Exported $container_name ($size)"
        else
            print_error "Failed to export $container_name"
        fi
    done
}

# Save images as tar files
save_images() {
    print_header "Saving Docker images"
    
    mkdir -p "$EXPORT_DIR"
    
    # Get list of images used by docker-compose
    local images=$(docker-compose config | grep 'image:' | awk '{print $2}' | sort -u)
    
    for image in $images; do
        local image_file=$(echo "$image" | tr '/:' '_')
        local export_file="$EXPORT_DIR/${image_file}_$(date +%Y%m%d_%H%M%S).tar"
        
        print_info "Saving image: $image..."
        docker save "$image" -o "$export_file"
        
        if [ -f "$export_file" ]; then
            local size=$(du -h "$export_file" | cut -f1)
            print_success "Saved $image ($size)"
        else
            print_error "Failed to save $image"
        fi
    done
}

# Load images from tar files
load_images() {
    print_header "Loading Docker images"
    
    if [ ! -d "$EXPORT_DIR" ]; then
        print_error "Export directory not found: $EXPORT_DIR"
        exit 1
    fi
    
    local tar_files=$(find "$EXPORT_DIR" -name "*.tar" -type f)
    
    if [ -z "$tar_files" ]; then
        print_warning "No tar files found in $EXPORT_DIR"
        return
    fi
    
    for tar_file in $tar_files; do
        print_info "Loading image from $(basename "$tar_file")..."
        docker load -i "$tar_file"
        print_success "Loaded $(basename "$tar_file")"
    done
}

# Create complete backup (volumes + config)
backup_all() {
    print_header "Creating complete backup"
    
    local backup_name="${PROJECT_NAME}_backup_$(date +%Y%m%d_%H%M%S)"
    local backup_path="$BACKUP_DIR/$backup_name"
    
    mkdir -p "$backup_path"
    
    # Backup volumes
    print_info "Backing up Docker volumes..."
    local volumes=$(docker volume ls -q | grep "^${PROJECT_NAME}")
    
    for volume in $volumes; do
        print_info "Backing up volume: $volume"
        docker run --rm \
            -v "$volume":/source \
            -v "$(pwd)/$backup_path":/backup \
            alpine tar czf "/backup/${volume}.tar.gz" -C /source .
        print_success "Backed up $volume"
    done
    
    # Backup configuration files
    print_info "Backing up configuration files..."
    cp -r config "$backup_path/"
    cp .env "$backup_path/" 2>/dev/null || true
    cp docker-compose.yml "$backup_path/"
    
    # Create manifest
    cat > "$backup_path/manifest.json" <<EOF
{
    "backup_date": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
    "project_name": "$PROJECT_NAME",
    "docker_version": "$(docker --version)",
    "docker_compose_version": "$(docker-compose --version)",
    "volumes": $(docker volume ls -q | grep "^${PROJECT_NAME}" | jq -R . | jq -s .),
    "images": $(docker-compose config | grep 'image:' | awk '{print $2}' | jq -R . | jq -s .)
}
EOF
    
    # Create archive
    print_info "Creating archive..."
    tar czf "$BACKUP_DIR/${backup_name}.tar.gz" -C "$BACKUP_DIR" "$backup_name"
    rm -rf "$backup_path"
    
    print_success "Backup created: $BACKUP_DIR/${backup_name}.tar.gz"
}

# Restore from backup
restore_backup() {
    local backup_file="$1"
    
    if [ ! -f "$backup_file" ]; then
        print_error "Backup file not found: $backup_file"
        exit 1
    fi
    
    print_header "Restoring from backup: $backup_file"
    
    local temp_dir=$(mktemp -d)
    tar xzf "$backup_file" -C "$temp_dir"
    
    local backup_name=$(basename "$backup_file" .tar.gz)
    local restore_path="$temp_dir/$backup_name"
    
    # Restore volumes
    print_info "Restoring volumes..."
    for volume_tar in "$restore_path"/*.tar.gz; do
        if [ -f "$volume_tar" ]; then
            local volume_name=$(basename "$volume_tar" .tar.gz)
            print_info "Restoring volume: $volume_name"
            
            docker volume create "$volume_name" || true
            docker run --rm \
                -v "$volume_name":/target \
                -v "$(dirname "$volume_tar")":/backup \
                alpine tar xzf "/backup/$(basename "$volume_tar")" -C /target
            
            print_success "Restored $volume_name"
        fi
    done
    
    # Restore configuration
    print_info "Restoring configuration files..."
    cp -r "$restore_path/config" ./
    cp "$restore_path/.env" ./ 2>/dev/null || true
    
    rm -rf "$temp_dir"
    
    print_success "Restore completed"
}

# Check service health
check_health() {
    print_header "Checking service health"
    
    docker-compose ps
    
    echo ""
    print_info "Service status:"
    
    for service in app webserver mysql mongodb redis; do
        local container="${PROJECT_NAME}_${service}"
        if docker ps --format '{{.Names}}' | grep -q "^${container}$"; then
            local health=$(docker inspect --format='{{.State.Health.Status}}' "$container" 2>/dev/null || echo "unknown")
            if [ "$health" = "healthy" ] || [ "$health" = "unknown" ]; then
                print_success "$service: running"
            else
                print_warning "$service: $health"
            fi
        else
            print_error "$service: not running"
        fi
    done
}

# Detect available services
detect_services() {
    print_header "Detecting available services"
    
    docker-compose exec -T app php -r "
        require_once 'vendor/autoload.php';
        \$detector = new PIMP\Services\ServiceDetector();
        \$results = \$detector->testAllServices();
        print_r(\$results);
    "
}

# Show usage
show_usage() {
    cat <<EOF
Docker Manager - Container Management Tool

USAGE:
    ./scripts/docker-manager.sh [COMMAND] [OPTIONS]

COMMANDS:
    switch-env <mode>       Switch environment configuration
                           Modes: docker, native, hybrid
    
    start [profile]        Start services with profile
                           Profiles: full, app-only, db-only, frontend
    
    stop                   Stop all services
    
    export-containers      Export all containers as tar files
    
    save-images           Save Docker images as tar files
    
    load-images           Load Docker images from tar files
    
    backup                Create complete backup (volumes + config)
    
    restore <file>        Restore from backup file
    
    health                Check health of all services
    
    detect                Detect available services (native/docker)
    
    clean                 Remove all containers, volumes, and images
    
    rebuild               Rebuild containers from scratch

EXAMPLES:
    # Switch to native MySQL but Docker for other services
    ./scripts/docker-manager.sh switch-env hybrid
    
    # Start only app and webserver (use native databases)
    ./scripts/docker-manager.sh start app-only
    
    # Export all containers for transfer
    ./scripts/docker-manager.sh export-containers
    
    # Save all images
    ./scripts/docker-manager.sh save-images
    
    # Create backup before migration
    ./scripts/docker-manager.sh backup
    
    # Check service health
    ./scripts/docker-manager.sh health

EOF
}

# Clean everything
clean_all() {
    print_header "Cleaning all Docker resources"
    
    read -p "This will remove all containers, volumes, and images. Continue? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_info "Cancelled"
        exit 0
    fi
    
    docker-compose down -v --rmi all
    print_success "Cleaned all resources"
}

# Rebuild from scratch
rebuild_all() {
    print_header "Rebuilding all containers"
    
    docker-compose down
    docker-compose build --no-cache
    docker-compose up -d
    
    print_success "Rebuild completed"
}

# Main command router
main() {
    local command="${1:-help}"
    shift || true
    
    case "$command" in
        switch-env)
            switch_env "$@"
            ;;
        start)
            start_services "$@"
            ;;
        stop)
            stop_services
            ;;
        export-containers)
            export_containers
            ;;
        save-images)
            save_images
            ;;
        load-images)
            load_images
            ;;
        backup)
            backup_all
            ;;
        restore)
            restore_backup "$@"
            ;;
        health)
            check_health
            ;;
        detect)
            detect_services
            ;;
        clean)
            clean_all
            ;;
        rebuild)
            rebuild_all
            ;;
        help|--help|-h)
            show_usage
            ;;
        *)
            print_error "Unknown command: $command"
            echo ""
            show_usage
            exit 1
            ;;
    esac
}

# Run main
main "$@"