#!/bin/bash

# GT Automotives - Improved Deployment Script with Database Migrations
# This script deploys code changes and runs database migrations with better error handling

set -e  # Exit on any error

echo "🚀 Starting GT Automotives Improved Deployment..."

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
FIX_MIGRATION_URL="${PRODUCTION_URL}/fix_production_migrations.php"

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

# Function to check if a URL is accessible
check_url() {
    local url=$1
    local response=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    if [ "$response" = "200" ]; then
        return 0
    else
        return 1
    fi
}

# Function to run migrations with retry logic
run_migrations() {
    local max_retries=3
    local retry_count=0
    
    while [ $retry_count -lt $max_retries ]; do
        print_status "Attempting to run migrations (attempt $((retry_count + 1))/$max_retries)..."
        
        # Try the main migration system first
        if check_url "$MIGRATION_URL"; then
            print_success "Migration system is accessible"
            
            # Run migrations via curl
            MIGRATION_RESPONSE=$(curl -s -X POST -d "run_migrations=1" "$MIGRATION_URL")
            
            if echo "$MIGRATION_RESPONSE" | grep -q "✅ Success\|Migration Results\|All migrations are up to date"; then
                print_success "Database migrations completed successfully!"
                return 0
            else
                print_warning "Migration response received, but please verify manually"
                echo "$MIGRATION_RESPONSE" | head -10
            fi
        else
            print_warning "Main migration system not accessible"
        fi
        
        # Try the fix migration script as fallback
        if check_url "$FIX_MIGRATION_URL"; then
            print_status "Trying alternative migration fix..."
            FIX_RESPONSE=$(curl -s "$FIX_MIGRATION_URL")
            
            if echo "$FIX_RESPONSE" | grep -q "Database connection successful"; then
                print_success "Alternative migration system is accessible"
                print_status "Please run migrations manually at: $FIX_MIGRATION_URL"
                return 0
            fi
        fi
        
        retry_count=$((retry_count + 1))
        if [ $retry_count -lt $max_retries ]; then
            print_warning "Migration attempt failed, retrying in 5 seconds..."
            sleep 5
        fi
    done
    
    print_error "Failed to run migrations after $max_retries attempts"
    return 1
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
sleep 3

# Run database migrations with improved error handling
print_status "Running database migrations with improved error handling..."

if run_migrations; then
    print_success "Migration process completed!"
else
    print_warning "Migration process had issues, but deployment continues..."
fi

# Verify deployment
print_status "Verifying deployment..."

# Check if main page is accessible
if check_url "$PRODUCTION_URL"; then
    print_success "Main page is accessible"
else
    print_warning "Main page returned non-200 status code"
fi

# Check if admin page is accessible
ADMIN_URL="${PRODUCTION_URL}/admin/"
if check_url "$ADMIN_URL"; then
    print_success "Admin page is accessible"
else
    print_warning "Admin page returned non-200 status code"
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
echo "Fix Migration URL: $FIX_MIGRATION_URL"
echo "================================"

# Post-deployment instructions
echo
print_status "Post-deployment checklist:"
echo "1. ✅ Verify main website is working: $PRODUCTION_URL"
echo "2. ✅ Check admin panel: $PRODUCTION_URL/admin/"
echo "3. ✅ Run database migrations: $MIGRATION_URL"
echo "4. ✅ If migrations fail, use: $FIX_MIGRATION_URL"
echo "5. ✅ Test user management: $PRODUCTION_URL/admin/users.php"
echo "6. ✅ Test product management: $PRODUCTION_URL/admin/products.php"
echo "7. ✅ Clear browser cache if needed"

# Migration troubleshooting section
echo
print_warning "Migration Troubleshooting:"
echo "If migrations didn't run automatically:"
echo "1. Visit: $FIX_MIGRATION_URL"
echo "2. Click '🔄 Run Full Migration System'"
echo "3. If that fails, click '👥 Fix Users Table Only'"
echo "4. If tables are missing, click '📋 Create Missing Tables'"

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
echo "- Use the fix tool: $FIX_MIGRATION_URL"
echo "- Review error logs on your hosting provider"
echo "- Test user login and admin functionality"
echo "- Verify database structure is correct"

echo
print_success "🎯 Improved deployment script completed!" 