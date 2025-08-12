#!/bin/bash

# Location.php Production Fix Deployment Script
# This script deploys the fixed location functionality to production

echo "🚀 Starting Location.php Production Fix Deployment"
echo "=================================================="

# Set variables
REPO_DIR="/Users/vishaltoora/projects/gt-automotives-web-page"
PRODUCTION_DIR="/var/www/html"  # Adjust this to your production path
BACKUP_DIR="/tmp/gt_automotives_backup_$(date +%Y%m%d_%H%M%S)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the correct directory
if [ ! -f "$REPO_DIR/admin/locations.php" ]; then
    print_error "Repository directory not found or incorrect: $REPO_DIR"
    exit 1
fi

print_status "Repository directory confirmed: $REPO_DIR"

# Create backup directory
print_status "Creating backup directory: $BACKUP_DIR"
mkdir -p "$BACKUP_DIR"

# Check if production directory exists
if [ ! -d "$PRODUCTION_DIR" ]; then
    print_warning "Production directory not found: $PRODUCTION_DIR"
    print_status "Please update the PRODUCTION_DIR variable in this script"
    print_status "Current production path: $PRODUCTION_DIR"
    read -p "Enter correct production path: " PRODUCTION_DIR
fi

# Backup current production files
print_status "Creating backup of current production files..."
if [ -d "$PRODUCTION_DIR" ]; then
    cp -r "$PRODUCTION_DIR/admin/locations.php" "$BACKUP_DIR/" 2>/dev/null || print_warning "Could not backup locations.php"
    cp -r "$PRODUCTION_DIR/admin/add_location.php" "$BACKUP_DIR/" 2>/dev/null || print_warning "Could not backup add_location.php"
    cp -r "$PRODUCTION_DIR/admin/edit_location.php" "$BACKUP_DIR/" 2>/dev/null || print_warning "Could not backup edit_location.php"
    print_status "Backup created in: $BACKUP_DIR"
else
    print_warning "Production directory not accessible, skipping backup"
fi

# Deploy the fixed files
print_status "Deploying fixed location files..."

# List of files to deploy
FILES_TO_DEPLOY=(
    "admin/locations.php"
    "admin/add_location.php"
    "admin/edit_location.php"
)

# Deploy each file
for file in "${FILES_TO_DEPLOY[@]}"; do
    if [ -f "$REPO_DIR/$file" ]; then
        if [ -d "$PRODUCTION_DIR" ]; then
            # Create directory structure if it doesn't exist
            mkdir -p "$PRODUCTION_DIR/$(dirname "$file")"
            
            # Copy file to production
            cp "$REPO_DIR/$file" "$PRODUCTION_DIR/$file"
            
            if [ $? -eq 0 ]; then
                print_status "✅ Deployed: $file"
            else
                print_error "❌ Failed to deploy: $file"
            fi
        else
            print_warning "Production directory not accessible, showing file contents for manual deployment:"
            echo "=== $file ==="
            cat "$REPO_DIR/$file"
            echo "=== END $file ==="
        fi
    else
        print_error "❌ Source file not found: $REPO_DIR/$file"
    fi
done

# Set proper permissions
if [ -d "$PRODUCTION_DIR" ]; then
    print_status "Setting file permissions..."
    chmod 644 "$PRODUCTION_DIR/admin/locations.php" 2>/dev/null
    chmod 644 "$PRODUCTION_DIR/admin/add_location.php" 2>/dev/null
    chmod 644 "$PRODUCTION_DIR/admin/edit_location.php" 2>/dev/null
    print_status "✅ Permissions set"
fi

# Create deployment verification script
print_status "Creating deployment verification script..."
cat > "$REPO_DIR/verify_deployment.php" << 'EOF'
<?php
// Deployment Verification Script
echo "<h1>Location.php Deployment Verification</h1>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    require_once 'includes/db_connect.php';
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test locations functionality
echo "<h2>Locations Functionality Test</h2>";
try {
    $result = $conn->query("SELECT * FROM locations ORDER BY name ASC");
    if ($result) {
        echo "✅ Locations query successful<br>";
        echo "Found " . $result->num_rows . " locations<br>";
        
        while ($location = $result->fetch_assoc()) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
            echo "<strong>" . htmlspecialchars($location['name']) . "</strong><br>";
            echo "Description: " . htmlspecialchars($location['description'] ?? 'N/A') . "<br>";
            echo "Address: " . htmlspecialchars($location['address']) . "<br>";
            echo "Contact: " . htmlspecialchars($location['contact_person'] ?? 'N/A') . "<br>";
            echo "Status: " . ($location['is_active'] ? 'Active' : 'Inactive') . "<br>";
            echo "</div>";
        }
    } else {
        echo "❌ Locations query failed: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error testing locations: " . $e->getMessage() . "<br>";
}

// Test file existence
echo "<h2>File Existence Test</h2>";
$files_to_check = [
    'admin/locations.php',
    'admin/add_location.php',
    'admin/edit_location.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file not found<br>";
    }
}

echo "<h2>Deployment Complete!</h2>";
echo "If all tests pass, the location functionality should be working correctly.";
?>
EOF

print_status "✅ Deployment verification script created: verify_deployment.php"

# Summary
echo ""
echo "🎉 Deployment Summary"
echo "===================="
print_status "Files deployed:"
for file in "${FILES_TO_DEPLOY[@]}"; do
    echo "  - $file"
done

echo ""
print_status "Next steps:"
echo "1. Access your production site"
echo "2. Navigate to: /admin/locations.php"
echo "3. Test the location management functionality"
echo "4. Run verification script: /verify_deployment.php"

echo ""
print_status "Backup location: $BACKUP_DIR"
print_status "If issues occur, restore from backup"

echo ""
echo "🚀 Deployment completed successfully!" 