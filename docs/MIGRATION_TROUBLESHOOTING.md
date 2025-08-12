# Migration Script Troubleshooting Guide

## 🚨 **Issue: Migration Script Did Not Run on Production**

### **Root Cause Analysis**

The migration script may not have run on production due to several common issues:

1. **PHP Syntax Error** - Fixed the undefined variable `$Nq` in `database/migrations.php`
2. **Database Connection Issues** - Production database credentials or connectivity problems
3. **File Permission Issues** - Migration files not accessible on production
4. **Deployment Script Issues** - The deployment script may have failed silently
5. **Hosting Provider Restrictions** - Some hosting providers block certain operations

## 🔧 **Immediate Solutions**

### **Solution 1: Use the Fix Tool (Recommended)**

1. **Upload the fix tool** to your production server:

   ```
   fix_production_migrations.php
   ```

2. **Visit the fix tool** in your browser:

   ```
   https://your-domain.com/fix_production_migrations.php
   ```

3. **Follow the step-by-step process**:

   - Step 1: Database connection test
   - Step 2: Migration system check
   - Step 3: Database structure analysis
   - Step 4: Users table structure check
   - Step 5: Choose fix option

4. **Choose the appropriate fix**:
   - **🔄 Run Full Migration System** - For complete migration
   - **👥 Fix Users Table Only** - For users table issues
   - **📋 Create Missing Tables** - For missing table issues

### **Solution 2: Manual Migration**

1. **Visit the migration system**:

   ```
   https://your-domain.com/database/migrations.php
   ```

2. **Click "🔄 Run Pending Migrations"**

3. **Review the results** and check for any error messages

### **Solution 3: Use the Simple Fix Script**

1. **Visit the simple fix script**:

   ```
   https://your-domain.com/run_production_migrations.php
   ```

2. **Click "⚠️ Fix Users Table Structure"**

## 🛠️ **Advanced Troubleshooting**

### **Check Database Connection**

```bash
# Test database connection from command line
mysql -u your_username -p your_database_name -e "SELECT 1;"
```

### **Check File Permissions**

```bash
# On your production server
ls -la database/
ls -la includes/
chmod 644 database/migrations.php
chmod 644 includes/db_connect.php
```

### **Check PHP Error Logs**

```bash
# Check PHP error logs
tail -f /var/log/php8.2-fpm.log
# or
tail -f /var/log/apache2/error.log
```

### **Test Migration System Locally**

```bash
# Test the migration system locally first
curl -s http://localhost:8001/database/migrations.php
```

## 📋 **Deployment Checklist**

### **Before Deployment**

- [ ] Test migrations locally
- [ ] Backup production database
- [ ] Commit all changes including the fixed migration files
- [ ] Update production URL in deployment script
- [ ] Ensure all files are uploaded to production

### **After Deployment**

- [ ] Visit `fix_production_migrations.php` on production
- [ ] Run the diagnostic steps
- [ ] Execute appropriate fix
- [ ] Verify admin panel works
- [ ] Test user management
- [ ] Test product management

## 🔍 **Common Error Messages and Solutions**

### **Error: "Undefined variable $Nq"**

**Solution**: Fixed in `database/migrations.php` - the password hash was being interpreted as a PHP variable.

### **Error: "Database connection failed"**

**Solutions**:

- Check database credentials in `includes/db_connect.php`
- Verify database server is running
- Check if database exists
- Verify user permissions

### **Error: "Table doesn't exist"**

**Solutions**:

- Run the migration system
- Use the fix tool to create missing tables
- Check if migrations table exists

### **Error: "Permission denied"**

**Solutions**:

- Check file permissions (should be 644 for files, 755 for directories)
- Check web server user permissions
- Verify database user has CREATE privileges

## 🚀 **Improved Deployment Process**

### **Use the Improved Deployment Script**

1. **Update your production URL** in `deploy_with_migrations_improved.sh`:

   ```bash
   PRODUCTION_URL="https://your-actual-domain.com"
   ```

2. **Run the improved deployment script**:

   ```bash
   ./deploy_with_migrations_improved.sh
   ```

3. **The script will**:
   - Push code changes to GitHub
   - Try to run migrations automatically
   - Provide fallback options if migrations fail
   - Give detailed feedback on what worked and what didn't

## 📞 **Emergency Fixes**

### **If Nothing Works**

1. **Manual Database Setup**:

   ```sql
   -- Create users table manually
   CREATE TABLE IF NOT EXISTS users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(255) UNIQUE NOT NULL,
       first_name VARCHAR(255) NOT NULL DEFAULT '',
       last_name VARCHAR(255) NOT NULL DEFAULT '',
       email VARCHAR(255),
       password VARCHAR(255) NOT NULL,
       is_admin TINYINT(1) DEFAULT 0,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   );
   ```

2. **Create Admin User**:
   ```sql
   INSERT INTO users (username, first_name, last_name, password, email, is_admin)
   VALUES ('admin', 'Admin', 'User', '$2y$10$Nq/VTTeC7NqIrdWUwJJvR.mRXMy8YH3wF5WKIUG63yzsCEP3Cq34q', 'admin@gtautomotives.com', 1);
   ```

## 🎯 **Prevention for Future Deployments**

### **Best Practices**

1. **Always test migrations locally** before deploying
2. **Use the improved deployment script** with better error handling
3. **Keep database backups** before major changes
4. **Monitor deployment logs** for any issues
5. **Have a rollback plan** ready

### **Monitoring**

- Set up alerts for database connection issues
- Monitor migration execution logs
- Regular database structure validation
- Automated testing of admin functionality

## 📞 **Support**

If you're still experiencing issues:

1. **Run the diagnostic tool**: `fix_production_migrations.php`
2. **Check error logs** on your hosting provider
3. **Test database connectivity** manually
4. **Verify file permissions** and accessibility
5. **Contact hosting provider** if it's a server configuration issue

---

**Remember**: Always backup your database before running migrations in production!
