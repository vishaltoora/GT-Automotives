# GT Automotives Production Troubleshooting Guide

## 🚨 Critical Issues Identified

Based on the production debug report, the following issues need immediate attention:

### 1. Missing Environment Files

- **Issue**: `.env` and `.env.production` files not found on production server
- **Impact**: Application cannot load configuration, may use fallback values
- **Solution**: Use the new `production_config.php` file as fallback

### 2. Database Connection Warning

- **Issue**: `Undefined property: mysqli::$database` warning
- **Impact**: Debug information incomplete, potential errors
- **Solution**: Fixed in `production_debug.php` by removing invalid property reference

### 3. 302 Redirect on Admin Pages

- **Issue**: `/admin/create_sale.php` returns 302 redirect
- **Impact**: Users cannot access admin functionality
- **Solution**: Check authentication and session handling

## 🔧 Immediate Fixes Applied

### ✅ Fixed Issues

1. **Database Property Warning**: Removed invalid `$conn->database` reference
2. **Production Configuration**: Created `production_config.php` fallback
3. **Database Connection**: Enhanced with production config support
4. **Authentication**: Improved session security for production
5. **Server Configuration**: Added production-ready `.htaccess`
6. **Deployment Script**: Created automated setup script

## 📋 Production Deployment Steps

### Step 1: Upload Fixed Files

```bash
# Upload these files to production server:
- includes/production_config.php
- includes/db_connect.php (updated)
- includes/auth.php (updated)
- admin/production_debug.php (updated)
- .htaccess
- scripts/deploy_production.sh
```

### Step 2: Run Deployment Script

```bash
# SSH into production server
ssh user@your-server

# Navigate to project directory
cd /opt/bitnami/apache/htdocs

# Make script executable and run
chmod +x scripts/deploy_production.sh
./scripts/deploy_production.sh
```

### Step 3: Verify Configuration

```bash
# Check if production config is loaded
php -r "require_once 'includes/production_config.php'; echo isProduction() ? 'Production' : 'Development';"

# Test database connection
php -r "require_once 'includes/db_connect.php'; echo isset(\$conn) ? 'Connected' : 'Failed';"
```

## 🐛 Troubleshooting Specific Issues

### Issue: 302 Redirect on Admin Pages

**Symptoms**: Admin pages return 302 redirect instead of content

**Causes**:

1. User not authenticated
2. Session expired
3. Authentication redirect loop
4. Incorrect file paths

**Debug Steps**:

1. Check if user is logged in:

   ```php
   // Add to admin pages for debugging
   var_dump($_SESSION);
   var_dump(session_id());
   ```

2. Verify session configuration:

   ```bash
   # Check session directory permissions
   ls -la /opt/bitnami/php/var/run/session

   # Check session files
   ls -la /opt/bitnami/php/var/run/session/sess_*
   ```

3. Test authentication flow:

   ```bash
   # Access login page directly
   curl -I https://www.gt-automotives.com/admin/login.php

   # Check if login.php exists
   ls -la /opt/bitnami/apache/htdocs/admin/login.php
   ```

### Issue: Database Connection Problems

**Symptoms**: Database errors, connection failures

**Debug Steps**:

1. Test database connectivity:

   ```bash
   mysql -u gtadmin -p -h localhost gt_automotives
   ```

2. Check database user permissions:

   ```sql
   SHOW GRANTS FOR 'gtadmin'@'localhost';
   ```

3. Verify database exists:
   ```sql
   SHOW DATABASES LIKE 'gt_automotives';
   ```

### Issue: File Permission Errors

**Symptoms**: 403 Forbidden errors, file access denied

**Fix**:

```bash
# Set correct permissions
chmod 755 /opt/bitnami/apache/htdocs
chmod 755 /opt/bitnami/apache/htdocs/admin
chmod 755 /opt/bitnami/apache/htdocs/includes
chmod 644 /opt/bitnami/apache/htdocs/admin/*.php
chmod 644 /opt/bitnami/apache/htdocs/includes/*.php

# Make storage writable
chmod 775 /opt/bitnami/apache/htdocs/storage/logs
chmod 775 /opt/bitnami/apache/htdocs/storage/cache
chmod 775 /opt/bitnami/apache/htdocs/uploads
```

## 🔍 Debugging Tools

### 1. Production Debug Page

Access: `https://www.gt-automotives.com/admin/production_debug.php`

This page provides:

- Server information
- File system status
- Database connection test
- Session status
- Authentication status
- Environment variables
- PHP configuration

### 2. Database Test Script

```bash
php /opt/bitnami/apache/htdocs/admin/db_test.php
```

### 3. Session Test Script

```bash
php /opt/bitnami/apache/htdocs/admin/session_test.php
```

## 📊 Monitoring and Logs

### Key Log Files

- **Apache Access Log**: `/opt/bitnami/apache/logs/gt_automotives_access.log`
- **Apache Error Log**: `/opt/bitnami/apache/logs/gt_automotives_error.log`
- **PHP Error Log**: `/opt/bitnami/php/logs/php_errors.log`
- **Application Logs**: `/opt/bitnami/apache/htdocs/storage/logs/`

### Monitoring Commands

```bash
# Monitor access logs in real-time
tail -f /opt/bitnami/apache/logs/gt_automotives_access.log

# Monitor error logs
tail -f /opt/bitnami/apache/logs/gt_automotives_error.log

# Check disk space
df -h /opt/bitnami/apache/htdocs

# Check memory usage
free -h
```

## 🚀 Performance Optimization

### 1. Enable Apache Modules

```bash
# Enable required modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
sudo systemctl reload apache2
```

### 2. PHP Optimization

```ini
; Add to php.ini or .htaccess
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
```

### 3. Database Optimization

```sql
-- Optimize tables
OPTIMIZE TABLE tires, sales, users, services;

-- Check table status
SHOW TABLE STATUS;
```

## 🔒 Security Checklist

- [ ] HTTPS enforced
- [ ] Security headers configured
- [ ] Sensitive files protected
- [ ] Session security enabled
- [ ] CSRF protection active
- [ ] Rate limiting configured
- [ ] Error reporting disabled in production
- [ ] File permissions secure

## 📞 Support Information

If issues persist after applying these fixes:

1. **Check Error Logs**: Review all log files for specific error messages
2. **Verify Configuration**: Ensure all configuration files are properly uploaded
3. **Test Incrementally**: Test each component separately
4. **Contact Support**: Provide specific error messages and log excerpts

## 📝 Post-Deployment Checklist

- [ ] Admin panel accessible
- [ ] User authentication working
- [ ] Database queries successful
- [ ] File uploads functional
- [ ] Error logs clean
- [ ] Performance acceptable
- [ ] Security headers present
- [ ] HTTPS working correctly

---

**Last Updated**: August 12, 2025  
**Version**: 1.0  
**Status**: Production Ready
