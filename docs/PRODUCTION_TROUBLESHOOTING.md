# Products.php Production Troubleshooting Guide

## Quick Diagnostic Steps

### 1. Run the Diagnostic Script

Access this URL in your browser to get a comprehensive diagnostic report:

```
http://your-domain.com/debug_products.php
```

### 2. Test the Simplified Version

Try the fixed version with better error handling:

```
http://your-domain.com/products_fixed.php
```

### 3. Test Basic Functionality

Try the simple test script:

```
http://your-domain.com/test_products_simple.php
```

## Common Issues and Solutions

### Issue 1: Database Connection Failed

**Symptoms**: Page shows blank or database error messages
**Solutions**:

- Check database credentials in `includes/db_connect.php`
- Verify database server is running
- Check if database `gt_automotives` exists
- Verify user `gtadmin` has proper permissions

### Issue 2: Missing Database Tables

**Symptoms**: "No products found" message
**Solutions**:

- Run database setup: `http://your-domain.com/create_tables.php?action=create_tables`
- Import schema: `mysql -u gtadmin -p gt_automotives < database/schema.sql`

### Issue 3: File Path Issues

**Symptoms**: PHP errors about missing files
**Solutions**:

- Verify these files exist:
  - `includes/error_handler.php`
  - `includes/db_connect.php`
  - `css/style.css`
- Check file permissions (should be 644 for files, 755 for directories)

### Issue 4: PHP Error Reporting Disabled

**Symptoms**: Blank page with no error messages
**Solutions**:

- Add to top of products.php:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

- Check server error logs

### Issue 5: Missing PHP Extensions

**Symptoms**: Fatal errors about missing functions
**Solutions**:

- Ensure these extensions are installed:
  - mysqli
  - gd
  - fileinfo
- Contact hosting provider if extensions are missing

## Immediate Fix

Replace your current `products.php` with `products_fixed.php`:

```bash
cp products_fixed.php products.php
```

## Debugging Commands

### Check Database Connection

```bash
mysql -u gtadmin -p'Vishal@1234#' -e "SELECT COUNT(*) FROM tires;" gt_automotives
```

### Check PHP Error Log

```bash
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

### Check File Permissions

```bash
ls -la includes/
ls -la css/
```

## Production Checklist

- [ ] Database server is running
- [ ] Database `gt_automotives` exists
- [ ] User `gtadmin` has proper permissions
- [ ] Tables `brands` and `tires` exist with data
- [ ] All required files exist and are accessible
- [ ] PHP extensions are installed
- [ ] Error reporting is enabled for debugging
- [ ] CSS file is loading properly

## Contact Information

If you need help with hosting-specific issues:

- Contact your hosting provider
- Check hosting control panel for PHP settings
- Verify database credentials in hosting panel
