.PHONY: help build up down restart logs shell migrate fresh seed test clean production-build

# Colors for terminal output
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[0;33m
NC := \033[0m # No Color

help: ## Show this help message
	@echo '$(BLUE)Available commands:$(NC)'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-20s$(NC) %s\n", $$1, $$2}'

build: ## Build Docker images
	@echo '$(BLUE)Building Docker images...$(NC)'
	docker-compose build

build-no-cache: ## Build Docker images without cache
	@echo '$(BLUE)Building Docker images without cache...$(NC)'
	docker-compose build --no-cache

up: ## Start all services
	@echo '$(BLUE)Starting services...$(NC)'
	docker-compose up -d
	@echo '$(GREEN)Services started! Access the app at http://localhost:8000$(NC)'

down: ## Stop all services
	@echo '$(YELLOW)Stopping services...$(NC)'
	docker-compose down

down-volumes: ## Stop all services and remove volumes
	@echo '$(YELLOW)Stopping services and removing volumes...$(NC)'
	docker-compose down -v

restart: down up ## Restart all services

logs: ## View logs from all services
	docker-compose logs -f

logs-app: ## View logs from app service only
	docker-compose logs -f app

shell: ## Open shell in app container
	docker-compose exec app sh

shell-root: ## Open shell in app container as root
	docker-compose exec -u root app sh

migrate: ## Run database migrations
	@echo '$(BLUE)Running migrations...$(NC)'
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh database migration (WARNING: drops all tables)
	@echo '$(YELLOW)Running fresh migrations (this will drop all tables)...$(NC)'
	docker-compose exec app php artisan migrate:fresh

seed: ## Seed the database
	@echo '$(BLUE)Seeding database...$(NC)'
	docker-compose exec app php artisan db:seed

fresh: migrate-fresh seed ## Fresh migration and seed

tinker: ## Open Laravel Tinker
	docker-compose exec app php artisan tinker

optimize: ## Optimize Laravel application
	@echo '$(BLUE)Optimizing application...$(NC)'
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

optimize-clear: ## Clear all optimization caches
	@echo '$(BLUE)Clearing optimization caches...$(NC)'
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	docker-compose exec app php artisan cache:clear

storage-link: ## Create storage symlink
	docker-compose exec app php artisan storage:link

key-generate: ## Generate application key
	docker-compose exec app php artisan key:generate

create-admin: ## Create Filament admin user
	docker-compose exec app php artisan make:filament-user

test: ## Run tests
	docker-compose exec app php artisan test

pnpm-install: ## Install pnpm dependencies locally (for development)
	pnpm install

pnpm-build: ## Build frontend assets locally
	pnpm run build

pnpm-dev: ## Run Vite dev server locally
	pnpm run dev

clean: ## Clean up Docker resources
	@echo '$(YELLOW)Cleaning up Docker resources...$(NC)'
	docker-compose down -v --remove-orphans
	docker system prune -f

production-build: ## Build production Docker image
	@echo '$(BLUE)Building production Docker image...$(NC)'
	docker build -t lexicon-bo-dashboard:latest .

production-run: production-build ## Build and run production image
	@echo '$(BLUE)Running production image...$(NC)'
	docker run -d \
		--name lexicon-bo-prod \
		-p 8080:80 \
		--env-file .env.docker \
		lexicon-bo-dashboard:latest

production-stop: ## Stop production container
	@echo '$(YELLOW)Stopping production container...$(NC)'
	docker stop lexicon-bo-prod || true
	docker rm lexicon-bo-prod || true

ps: ## Show running containers
	docker-compose ps

stats: ## Show container resource usage
	docker stats --no-stream

health: ## Check application health
	@echo '$(BLUE)Checking application health...$(NC)'
	@curl -f http://localhost:8000/api/health || echo '$(YELLOW)Health check failed$(NC)'

init: build up migrate storage-link optimize create-admin ## Initialize project (build, start, migrate, optimize)
	@echo '$(GREEN)Project initialized successfully!$(NC)'
	@echo '$(BLUE)Access the application at http://localhost:8000$(NC)'

backup-db: ## Backup database
	@echo '$(BLUE)Backing up database...$(NC)'
	docker-compose exec -T postgres pg_dump -U lexicon lexicon_bo_dashboard > backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo '$(GREEN)Database backup created!$(NC)'

restore-db: ## Restore database from backup (set BACKUP_FILE=/path/to/backup.sql)
	@if [ -z "$(BACKUP_FILE)" ]; then \
		echo '$(YELLOW)Please specify BACKUP_FILE=/path/to/backup.sql$(NC)'; \
		exit 1; \
	fi
	@echo '$(BLUE)Restoring database from $(BACKUP_FILE)...$(NC)'
	docker-compose exec -T postgres psql -U lexicon lexicon_bo_dashboard < $(BACKUP_FILE)
	@echo '$(GREEN)Database restored!$(NC)'
