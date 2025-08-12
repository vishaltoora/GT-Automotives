# Production Fix Guide for Create Sale Page

## Issue Summary

The create sale page (`/admin/create_sale.php`) appears empty in production, while working fine locally.

## Root Causes & Solutions

### 1. Database Connection Issues (Most Common)

**Problem**: Production database credentials or connection settings are incorrect.

**Solution**:

1. Check your production `.env` file:

   ```bash
   # On production server, verify these settings:
   DB_HOST=your_production_db_host
   DB_DATABASE=your_production_db_name
   DB_USERNAME=your_production_db_user
   DB_PASSWORD=your_production_db_password
   ```

2. Test database connection using the debug script:

   ```
   https://your-domain.com/admin/production_debug.php
   ```

3. Verify database server is accessible from your web server.

### 2. File Permissions & Path Issues

**Problem**: Web server cannot access required files or directories.

**Solution**:

1. Check file permissions:

   ```bash
   # On production server
   chmod 644 admin/create_sale.php
   chmod 644 includes/db_connect.php
   chmod 644 includes/auth.php
   chmod 755 admin/
   chmod 755 includes/
   ```

2. Verify file paths are correct:
   ```bash
   # Check if files exist
   ls -la admin/create_sale.php
   ls -la includes/db_connect.php
   ls -la includes/auth.php
   ```

### 3. Session Configuration Issues

**Problem**: Sessions are not working properly in production.

**Solution**:

1. Check session directory permissions:

   ```bash
   # On production server
   chmod 755 storage/framework/sessions/
   chown www-data:www-data storage/framework/sessions/
   ```

2. Verify session configuration in PHP:
   ```bash
   # Check session settings
   php -i | grep session
   ```

### 4. Error Display vs Logging

**Problem**: Errors are not visible in production due to error display being disabled.

**Solution**:

1. Check error logs:

   ```bash
   # On production server
   tail -f storage/logs/laravel.log
   tail -f /var/log/apache2/error.log  # or nginx error log
   ```

2. Temporarily enable error display for debugging:
   ```php
   // In create_sale.php, temporarily add:
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

## Step-by-Step Fix Process

### Step 1: Run Production Debug Script

1. Upload `admin/production_debug.php` to your production server
2. Visit: `https://your-domain.com/admin/production_debug.php`
3. Review all sections for errors

### Step 2: Check Database Connection

1. Verify database credentials in `.env`
2. Test connection manually:
   ```bash
   mysql -h your_host -u your_user -p your_database
   ```

### Step 3: Test File Access

1. Check if all required files exist
2. Verify file permissions
3. Test includes manually

### Step 4: Check Server Logs

1. Review PHP error logs
2. Check web server error logs
3. Look for specific error messages

### Step 5: Test Authentication

1. Try logging in to admin panel
2. Check if sessions are working
3. Verify user permissions

## Alternative Solutions

### Option 1: Use Production-Ready Version

Replace the current `create_sale.php` with `create_sale_production.php`:

```bash
# On production server
cp admin/create_sale_production.php admin/create_sale.php
```

### Option 2: Enable Debug Mode

Add debug parameter to see detailed errors:

```
https://your-domain.com/admin/create_sale.php?debug=1
```

### Option 3: Check Laravel Routes

If using Laravel routes, verify they're working:

```
https://your-domain.com/admin/sales/create
```

## Common Production Issues

### 1. Environment Variables

- `.env` file not loaded
- Wrong database credentials
- Missing required variables

### 2. Server Configuration

- PHP version mismatch
- Missing PHP extensions
- Incorrect document root

### 3. Database Issues

- Connection timeout
- Wrong host/port
- Firewall blocking connection
- Database server down

### 4. File System Issues

- Wrong file permissions
- Missing directories
- Path resolution problems

## Testing After Fix

1. **Test Database Connection**: Use `production_debug.php`
2. **Test Authentication**: Try logging in
3. **Test Create Sale**: Visit the page and check for errors
4. **Test Form Submission**: Try creating a test sale
5. **Check Logs**: Monitor for any remaining errors

## Emergency Fallback

If the page still doesn't work:

1. **Enable Error Display**:

   ```php
   // Add to top of create_sale.php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

2. **Check Browser Console**: Look for JavaScript errors
3. **Test with Simple Page**: Create a minimal test page
4. **Contact Hosting Support**: They may have server-specific issues

## Prevention

1. **Always test in staging environment first**
2. **Use environment-specific configuration files**
3. **Implement proper error logging in production**
4. **Regular backup and monitoring**
5. **Document deployment procedures**

## Support Commands

```bash
# Check PHP version
php -v

# Check PHP configuration
php -i | grep -E "(error|session|database)"

# Check file permissions
ls -la admin/
ls -la includes/

# Check database connection
mysql -h localhost -u username -p database_name -e "SELECT 1"

# Check web server status
sudo systemctl status apache2  # or nginx
sudo systemctl status mysql

# Check error logs
tail -f /var/log/apache2/error.log
tail -f storage/logs/laravel.log
```

## Quick Fix Checklist

- [ ] Upload `production_debug.php` and run it
- [ ] Check database credentials in `.env`
- [ ] Verify file permissions (644 for files, 755 for directories)
- [ ] Check server error logs
- [ ] Test database connection manually
- [ ] Verify session directory permissions
- [ ] Check if all required files exist
- [ ] Test authentication flow
- [ ] Review browser console for JavaScript errors

## Contact Information

If you need additional help:

1. Run the debug script and share the output
2. Check server error logs and share relevant messages
3. Verify your hosting environment details
4. Test with the production-ready version
