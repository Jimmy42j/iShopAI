# Clothing E-Commerce Platform Makefile

.PHONY: help install dev build test clean docker-build docker-up docker-down

# Default target
help:
	@echo "Available commands:"
	@echo "  install      - Install all dependencies"
	@echo "  dev          - Start development servers"
	@echo "  build        - Build all components"
	@echo "  test         - Run all tests"
	@echo "  clean        - Clean build artifacts"
	@echo "  docker-build - Build Docker images"
	@echo "  docker-up    - Start Docker containers"
	@echo "  docker-down  - Stop Docker containers"

# Install dependencies
install:
	@echo "Installing backend dependencies..."
	cd backend && composer install
	@echo "Installing frontend dependencies..."
	cd frontend && npm install
	@echo "Installing AI service dependencies..."
	cd ai-service && pip install -r requirements.txt

# Development servers
dev:
	@echo "Starting development servers..."
	@echo "Starting AI service..."
	cd ai-service && python app.py &
	@echo "Starting PHP backend..."
	cd backend && php -S localhost:8000 -t public &
	@echo "Starting React frontend..."
	cd frontend && npm run dev

# Build all components
build:
	@echo "Building frontend..."
	cd frontend && npm run build
	@echo "Optimizing backend..."
	cd backend && composer install --no-dev --optimize-autoloader

# Run tests
test:
	@echo "Running backend tests..."
	cd backend && composer test
	@echo "Running frontend tests..."
	cd frontend && npm test
	@echo "Testing AI service..."
	cd ai-service && python -m pytest tests/ || echo "No tests found for AI service"

# Clean build artifacts
clean:
	@echo "Cleaning build artifacts..."
	cd frontend && rm -rf dist node_modules/.cache
	cd backend && rm -rf vendor
	cd ai-service && rm -rf __pycache__ .pytest_cache

# Docker commands
docker-build:
	@echo "Building Docker images..."
	cd docker && docker-compose build

docker-up:
	@echo "Starting Docker containers..."
	cd docker && docker-compose up -d

docker-down:
	@echo "Stopping Docker containers..."
	cd docker && docker-compose down

docker-logs:
	@echo "Showing Docker logs..."
	cd docker && docker-compose logs -f

# Database setup
db-setup:
	@echo "Setting up database..."
	mysql -u root -p < database/setup.sql

# Production deployment
deploy:
	@echo "Deploying to production..."
	$(MAKE) build
	$(MAKE) docker-build
	$(MAKE) docker-up

# Development setup (first time)
setup:
	@echo "Setting up development environment..."
	$(MAKE) install
	@echo "Please set up your database and configure .env files"
	@echo "Then run 'make dev' to start development servers"
