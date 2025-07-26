# GT Automotives Deployment Troubleshooting Guide

## Quick Diagnosis

1. **Visit these URLs on your deployed server:**
   - `https://your-domain.com/debug.php` - Detailed system information
   - `https://your-domain.com/test.php` - Basic functionality test
   - `https://your-domain.com/fix_permissions.php` - Fix file permissions
   - `https://your-domain.com/products.php?debug=1` - Products page with debug info
   - `https://your-domain.com/admin/index.php?debug=1` - Admin page with debug info

## Common Issues and Solutions

### 1. Database Connection Issues

**Symptoms:**

- White screen or 500 error
- "Database connection failed" message
- Products/admin pages not loading

**Solutions:**

```bash
# On your server, run these commands:
sudo chown www-data:www-data database/gt_automotives.db
sudo chmod 644 database/gt_automotives.db
sudo chown www-data:www-data database/
sudo chmod 755 database/
```

### 2. File Permission Issues

**Symptoms:**

- Image uploads not working
- "Permission denied" errors
- Uploads directory not writable

**Solutions:**

```bash
# Create uploads directory with proper permissions
sudo mkdir -p uploads/compressed
sudo chown www-data:www-data uploads/
sudo chmod 755 uploads/
sudo chmod 755 uploads/compressed/
```

### 3. Missing PHP Extensions

**Symptoms:**

- Image compression not working
- "Class not found" errors
- GD library errors

**Solutions:**

```bash
# Install required PHP extensions
sudo apt-get update
sudo apt-get install php8.2-gd php8.2-mysqli php8.2-fileinfo php8.2-zip

# Restart web server
sudo systemctl restart apache2
# or for nginx:
sudo systemctl restart nginx
```

### 4. Composer Dependencies

**Symptoms:**

- "Class 'GTAutomotives\Utils\ImageCompressor' not found"
- Image compression features not working

**Solutions:**

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# If Composer is not installed:
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 5. Web Server Configuration

**Symptoms:**

- 404 errors for PHP files
- Static files not loading
- Rewrite rules not working

**Solutions:**

**For Apache:**

```apache
# Add to .htaccess or virtual host config
<Directory /var/www/html>
    AllowOverride All
    Require all granted
</Directory>

# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**For Nginx:**

```nginx
# Add to server block
location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
}
```

### 6. SSL/HTTPS Issues

**Symptoms:**

- Mixed content warnings
- CSS/JS not loading over HTTPS
- Security warnings

**Solutions:**

```bash
# Update .htaccess to force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Step-by-Step Troubleshooting

### Step 1: Check Basic Functionality

1. Visit `https://your-domain.com/test.php`
2. Look for any error messages
3. Check if database connection works

### Step 2: Check System Requirements

1. Visit `https://your-domain.com/debug.php`
2. Verify all required PHP extensions are loaded
3. Check file permissions

### Step 3: Fix Permissions

1. Run `https://your-domain.com/fix_permissions.php`
2. Check the output for any failed operations
3. Manually fix any permission issues

### Step 4: Test Individual Pages

1. Visit `https://your-domain.com/products.php?debug=1`
2. Visit `https://your-domain.com/admin/index.php?debug=1`
3. Look for specific error messages

### Step 5: Check Error Logs

```bash
# Check Apache error logs
sudo tail -f /var/log/apache2/error.log

# Check PHP error logs
sudo tail -f /var/log/php8.2-fpm.log

# Check application error logs
tail -f /var/www/html/error.log
```

## Environment-Specific Issues

### AWS Lightsail

- Ensure security groups allow HTTP/HTTPS traffic
- Check if the instance has enough disk space
- Verify the deployment script ran successfully

### Shared Hosting

- Check if MySQL is enabled
- Verify PHP version compatibility
- Ensure file upload limits are sufficient

### VPS/Dedicated Server

- Check if all required services are running
- Verify firewall settings
- Check disk space and memory usage

## Debugging Commands

```bash
# Check PHP version and extensions
php -v
php -m | grep -E "(mysqli|gd|fileinfo|zip)"

# Check file permissions
ls -la database/
ls -la uploads/

# Check web server status
sudo systemctl status apache2
sudo systemctl status nginx

# Check disk space
df -h

# Check memory usage
free -h
```

## Contact Information

If you're still experiencing issues after following this guide:

1. Run the debug scripts and note the output
2. Check the error logs for specific error messages
3. Provide the debug output when seeking help

## Quick Fixes

### Emergency Fix Script

```bash
#!/bin/bash
# Run this script on your server to fix common issues

# Fix permissions
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/

# Create uploads directory
sudo mkdir -p /var/www/html/uploads/compressed
sudo chown -R www-data:www-data /var/www/html/uploads/
sudo chmod -R 755 /var/www/html/uploads/

# Restart web server
sudo systemctl restart apache2
```

Save this as `fix_deployment.sh` and run it on your server.
