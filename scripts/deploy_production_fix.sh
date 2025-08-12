#!/bin/bash

# Production Fix Deployment Script
# This script deploys the users table fix to production

set -e  # Exit on any error

echo "🚀 Deploying Production Users Table Fix..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Configuration
PROJECT_NAME="GT-Automotives"
PRODUCTION_URL="https://your-domain.com"  # Update this with your actual domain
FIX_URL="${PRODUCTION_URL}/production_users_fix.php"

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

# Display deployment summary
echo
print_success "🎉 Deployment Summary"
echo "================================"
echo "Project: $PROJECT_NAME"
echo "Branch: $CURRENT_BRANCH"
echo "Commit: $COMMIT_HASH"
echo "Production URL: $PRODUCTION_URL"
echo "Fix URL: $FIX_URL"
echo "================================"

# Post-deployment instructions
echo
print_status "Post-deployment checklist:"
echo "1. ✅ Upload files to production server:"
echo "   - production_users_fix.php"
echo "   - database/migrations.php (fixed)"
echo "   - fix_production_migrations.php"
echo ""
echo "2. ✅ Run the production fix:"
echo "   Visit: $FIX_URL"
echo "   Click: '⚠️ Fix Users Table - Add Missing Columns'"
echo ""
echo "3. ✅ Verify the fix:"
echo "   - Check admin panel: $PRODUCTION_URL/admin/"
echo "   - Test user management: $PRODUCTION_URL/admin/users.php"
echo "   - Try adding a new user"
echo ""
echo "4. ✅ If automatic fix fails, use manual SQL:"
echo "   ALTER TABLE users ADD COLUMN first_name VARCHAR(255) NOT NULL DEFAULT '';"
echo "   ALTER TABLE users ADD COLUMN last_name VARCHAR(255) NOT NULL DEFAULT '';"

# Manual SQL instructions
echo
print_warning "Manual SQL Fix (if needed):"
echo "If the automatic fix doesn't work, run these SQL commands manually in your database:"
echo ""
echo "1. Add missing columns:"
echo "   ALTER TABLE users ADD COLUMN first_name VARCHAR(255) NOT NULL DEFAULT '';"
echo "   ALTER TABLE users ADD COLUMN last_name VARCHAR(255) NOT NULL DEFAULT '';"
echo ""
echo "2. Update existing users (if any):"
echo "   UPDATE users SET first_name = 'Admin', last_name = 'User' WHERE first_name = '' OR first_name IS NULL;"
echo ""
echo "3. Create admin user (if none exists):"
echo "   INSERT INTO users (username, first_name, last_name, password, email, is_admin) VALUES ('admin', 'Admin', 'User', '\$2y\$10\$Nq/VTTeC7NqIrdWUwJJvR.mRXMy8YH3wF5WKIUG63yzsCEP3Cq34q', 'admin@gtautomotives.com', 1);"

echo
print_success "Deployment completed successfully!"
print_status "Remember to run the fix on production to add the missing columns."

echo
print_warning "If you encounter any issues:"
echo "- Check the fix tool: $FIX_URL"
echo "- Review error logs on your hosting provider"
echo "- Test database connectivity manually"
echo "- Verify database user has ALTER TABLE permissions"

echo
print_success "🎯 Production fix deployment script completed!" 