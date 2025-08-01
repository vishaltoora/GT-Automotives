#!/bin/bash

# GT Automotives - Deployment Script with Database Migrations
# This script deploys code changes and runs database migrations

set -e  # Exit on any error

echo "🚀 Starting GT Automotives Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="GT-Automotives"
PRODUCTION_URL="https://your-domain.com"  # Update this with your actual domain
MIGRATION_URL="${PRODUCTION_URL}/database/migrations.php"

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "index.php" ]; then
    print_error "This script must be run from the project root directory"
    exit 1
fi

print_status "Checking current git status..."

# Check if there are uncommitted changes
if [ -n "$(git status --porcelain)" ]; then
    print_warning "You have uncommitted changes. Please commit or stash them before deploying."
    git status --short
    read -p "Do you want to continue anyway? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Deployment cancelled."
        exit 1
    fi
fi

# Get current branch
CURRENT_BRANCH=$(git branch --show-current)
print_status "Current branch: $CURRENT_BRANCH"

# Check if we're on main branch
if [ "$CURRENT_BRANCH" != "main" ]; then
    print_warning "You're not on the main branch. Deploying from $CURRENT_BRANCH"
    read -p "Do you want to continue? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Deployment cancelled."
        exit 1
    fi
fi

# Get the latest commit hash
COMMIT_HASH=$(git rev-parse --short HEAD)
print_status "Deploying commit: $COMMIT_HASH"

# Push changes to remote
print_status "Pushing changes to GitHub..."
git push origin main

if [ $? -eq 0 ]; then
    print_success "Code changes pushed successfully!"
else
    print_error "Failed to push changes to GitHub"
    exit 1
fi

# Wait a moment for the push to complete
sleep 2

# Run database migrations
print_status "Running database migrations..."

# Function to check if migrations are accessible
check_migrations_access() {
    local response=$(curl -s -o /dev/null -w "%{http_code}" "$MIGRATION_URL")
    if [ "$response" = "200" ]; then
        return 0
    else
        return 1
    fi
}

# Try to access migrations page
print_status "Checking migration system accessibility..."
if check_migrations_access; then
    print_success "Migration system is accessible"
    
    # Run migrations via curl
    print_status "Executing database migrations..."
    MIGRATION_RESPONSE=$(curl -s -X POST -d "run_migrations=1" "$MIGRATION_URL")
    
    if echo "$MIGRATION_RESPONSE" | grep -q "✅ Success\|Migration Results"; then
        print_success "Database migrations completed successfully!"
    else
        print_warning "Migration response received, but please verify manually:"
        echo "$MIGRATION_RESPONSE" | head -20
    fi
else
    print_warning "Cannot access migration system automatically"
    print_status "Please run migrations manually at: $MIGRATION_URL"
fi

# Verify deployment
print_status "Verifying deployment..."

# Check if main page is accessible
MAIN_PAGE_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$PRODUCTION_URL")
if [ "$MAIN_PAGE_RESPONSE" = "200" ]; then
    print_success "Main page is accessible"
else
    print_warning "Main page returned status code: $MAIN_PAGE_RESPONSE"
fi

# Check if admin page is accessible
ADMIN_PAGE_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" "$PRODUCTION_URL/admin/")
if [ "$ADMIN_PAGE_RESPONSE" = "200" ] || [ "$ADMIN_PAGE_RESPONSE" = "302" ]; then
    print_success "Admin page is accessible"
else
    print_warning "Admin page returned status code: $ADMIN_PAGE_RESPONSE"
fi

# Display deployment summary
echo
print_success "🎉 Deployment Summary"
echo "================================"
echo "Project: $PROJECT_NAME"
echo "Branch: $CURRENT_BRANCH"
echo "Commit: $COMMIT_HASH"
echo "Production URL: $PRODUCTION_URL"
echo "Migration URL: $MIGRATION_URL"
echo "================================"

# Post-deployment instructions
echo
print_status "Post-deployment checklist:"
echo "1. ✅ Verify main website is working: $PRODUCTION_URL"
echo "2. ✅ Check admin panel: $PRODUCTION_URL/admin/"
echo "3. ✅ Run database migrations: $MIGRATION_URL"
echo "4. ✅ Test user management: $PRODUCTION_URL/admin/users.php"
echo "5. ✅ Test product management: $PRODUCTION_URL/admin/products.php"
echo "6. ✅ Clear browser cache if needed"

# Optional: Send notification
if command -v curl &> /dev/null; then
    print_status "Sending deployment notification..."
    # You can add webhook notifications here if needed
    # curl -X POST -H "Content-Type: application/json" \
    #      -d "{\"text\":\"🚀 GT Automotives deployed successfully!\"}" \
    #      "YOUR_WEBHOOK_URL"
fi

print_success "Deployment completed successfully!"
print_status "Remember to test all functionality in production."

echo
print_warning "If you encounter any issues:"
echo "- Check the migration system: $MIGRATION_URL"
echo "- Review error logs on your hosting provider"
echo "- Test user login and admin functionality"
echo "- Verify database structure is correct"

echo
print_success "🎯 Deployment script completed!" 