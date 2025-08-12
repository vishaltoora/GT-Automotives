#!/bin/bash

# GT Automotives Production Deployment Script
# This script sets up the production environment on the server

echo "🚗 GT Automotives Production Deployment Script"
echo "=============================================="

# Set production paths
PROD_ROOT="/opt/bitnami/apache/htdocs"
PROD_ADMIN="$PROD_ROOT/admin"
PROD_INCLUDES="$PROD_ROOT/includes"
PROD_SCRIPTS="$PROD_ROOT/scripts"

echo "📁 Setting up production directories..."

# Create necessary directories if they don't exist
mkdir -p "$PROD_INCLUDES"
mkdir -p "$PROD_SCRIPTS"
mkdir -p "$PROD_ROOT/storage/logs"
mkdir -p "$PROD_ROOT/storage/cache"
mkdir -p "$PROD_ROOT/uploads"

echo "🔐 Setting proper file permissions..."

# Set proper permissions for production
chmod 644 "$PROD_ROOT/includes/*.php" 2>/dev/null || true
chmod 644 "$PROD_ROOT/admin/*.php" 2>/dev/null || true
chmod 755 "$PROD_ROOT/includes" 2>/dev/null || true
chmod 755 "$PROD_ROOT/admin" 2>/dev/null || true
chmod 755 "$PROD_ROOT/storage" 2>/dev/null || true
chmod 755 "$PROD_ROOT/uploads" 2>/dev/null || true

# Make storage directories writable by web server
chmod 775 "$PROD_ROOT/storage/logs" 2>/dev/null || true
chmod 775 "$PROD_ROOT/storage/cache" 2>/dev/null || true
chmod 775 "$PROD_ROOT/uploads" 2>/dev/null || true

echo "🗄️  Checking database connection..."

# Test database connection
if [ -f "$PROD_ROOT/includes/db_connect.php" ]; then
    echo "Testing database connection..."
    php -r "
    require_once '$PROD_ROOT/includes/db_connect.php';
    if (isset(\$conn) && \$conn->ping()) {
        echo '✅ Database connection successful\n';
    } else {
        echo '❌ Database connection failed\n';
    }
    "
else
    echo "❌ Database connection file not found"
fi

echo "🔍 Checking production configuration..."

# Check if production config exists
if [ -f "$PROD_ROOT/includes/production_config.php" ]; then
    echo "✅ Production configuration file found"
else
    echo "❌ Production configuration file missing"
fi

echo "📝 Checking environment files..."

# Check .env files
if [ -f "$PROD_ROOT/.env" ]; then
    echo "✅ .env file found"
else
    echo "⚠️  .env file not found (using production config fallback)"
fi

if [ -f "$PROD_ROOT/.env.production" ]; then
    echo "✅ .env.production file found"
else
    echo "⚠️  .env.production file not found (using production config fallback)"
fi

echo "🌐 Checking web server configuration..."

# Check Apache configuration
if command -v apache2ctl >/dev/null 2>&1; then
    echo "Testing Apache configuration..."
    apache2ctl -t 2>/dev/null && echo "✅ Apache configuration is valid" || echo "❌ Apache configuration has errors"
elif command -v httpd >/dev/null 2>&1; then
    echo "Testing HTTPD configuration..."
    httpd -t 2>/dev/null && echo "✅ HTTPD configuration is valid" || echo "❌ HTTPD configuration has errors"
else
    echo "⚠️  Apache/HTTPD not found in PATH"
fi

echo "🔒 Checking security settings..."

# Check if HTTPS is enabled
if [ -f "$PROD_ROOT/includes/production_config.php" ]; then
    php -r "
    require_once '$PROD_ROOT/includes/production_config.php';
    if (isProduction()) {
        echo '✅ Production environment detected\n';
        echo '🔒 HTTPS required: ' . (getProductionConfig('HTTPS_REQUIRED') ? 'Yes' : 'No') . '\n';
        echo '🛡️  CSRF protection: ' . (getProductionConfig('CSRF_PROTECTION') ? 'Yes' : 'No') . '\n';
    } else {
        echo '⚠️  Not in production environment\n';
    }
    "
fi

echo "📊 Checking file system..."

# Check disk space
echo "Disk space usage:"
df -h "$PROD_ROOT" | tail -1

# Check memory usage
echo "Memory usage:"
free -h | grep -E "Mem|Swap"

echo "✅ Production deployment check completed!"
echo ""
echo "📋 Next steps:"
echo "1. Test the admin panel at: https://www.gt-automotives.com/admin/"
echo "2. Check error logs at: $PROD_ROOT/storage/logs/"
echo "3. Verify database connectivity"
echo "4. Test user authentication"
echo "5. Monitor application performance"
echo ""
echo "🔧 If issues persist, check:"
echo "- Apache error logs: /opt/bitnami/apache/logs/error_log"
echo "- PHP error logs: /opt/bitnami/php/logs/php_errors.log"
echo "- Application logs: $PROD_ROOT/storage/logs/" 