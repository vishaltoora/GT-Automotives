# Database Migration System

## Overview

The GT Automotives project now includes a comprehensive database migration system to ensure consistent database structures across development and production environments. This system prevents the "unknown column" errors and other database structure mismatches.

## 🎯 **Why This System Exists**

### Problems Solved:

- ❌ "Unknown column first_name" errors
- ❌ Database structure mismatches between environments
- ❌ Missing tables in production
- ❌ Inconsistent data types and constraints
- ❌ Manual database setup errors

### Benefits:

- ✅ Consistent database structure across all environments
- ✅ Automatic table creation and column addition
- ✅ Version-controlled database changes
- ✅ Safe migration execution with rollback tracking
- ✅ Sample data insertion for testing

## 📋 **How It Works**

### 1. Migration Tracking

The system maintains a `database_migrations` table that tracks:

- Which migrations have been executed
- When they were executed
- Success/failure status
- Error messages for failed migrations

### 2. Migration Execution

- Migrations run in order (001, 002, 003, etc.)
- Only pending migrations are executed
- Failed migrations are logged with error details
- Safe execution with proper error handling

### 3. Automatic Deployment

The deployment script automatically:

- Pushes code changes to GitHub
- Runs pending database migrations
- Verifies deployment success
- Provides post-deployment checklist

## 🛠️ **Available Migrations**

### Core Tables:

1. **001_create_users_table** - Creates users table with proper structure
2. **002_add_missing_user_columns** - Adds missing columns to existing users table
3. **003_create_brands_table** - Creates brands table
4. **004_create_sizes_table** - Creates sizes table
5. **005_create_tires_table** - Creates tires table with foreign keys

### Business Logic Tables:

6. **006_create_sales_table** - Creates sales table
7. **007_create_sale_items_table** - Creates sale items table
8. **008_create_service_categories_table** - Creates service categories
9. **009_create_services_table** - Creates services table
10. **010_create_inquiries_table** - Creates inquiries table

### Data Setup:

11. **011_insert_default_admin_user** - Creates default admin user
12. **012_insert_sample_data** - Inserts sample brands and categories

## 🚀 **How to Use**

### Option 1: Automatic Deployment (Recommended)

```bash
# Make the script executable
chmod +x deploy_with_migrations.sh

# Run the deployment script
./deploy_with_migrations.sh
```

### Option 2: Manual Migration

1. Visit: `https://your-domain.com/database/migrations.php`
2. Click "🔄 Run Pending Migrations"
3. Review the results
4. Test the application

### Option 3: Individual Fix Scripts

- **Fix Users Table**: `admin/fix_users_table.php`
- **Debug Database**: `admin/debug_users.php`
- **Create Admin User**: `admin/create_admin_user.php`

## 📊 **Migration Status Dashboard**

The migration system provides a visual dashboard showing:

- ✅ **Executed Migrations** (Green)
- ⏳ **Pending Migrations** (Yellow)
- ❌ **Failed Migrations** (Red)

## 🔧 **Configuration**

### Update Production URL

Edit `deploy_with_migrations.sh`:

```bash
PRODUCTION_URL="https://your-actual-domain.com"
```

### Add New Migrations

To add a new migration:

1. **Add to `database/migrations.php`**:

```php
'013_your_migration_name' => [
    'description' => 'Description of what this migration does',
    'sql' => "YOUR SQL STATEMENT HERE"
]
```

2. **Follow naming convention**:

- Use 3-digit numbers: 001, 002, 013, 014, etc.
- Use descriptive names: `create_new_table`, `add_new_column`
- Include clear descriptions

## 🛡️ **Safety Features**

### Error Handling:

- Failed migrations are logged with error messages
- System continues with remaining migrations
- No data loss during migration process

### Rollback Protection:

- Uses `CREATE TABLE IF NOT EXISTS`
- Uses `ADD COLUMN IF NOT EXISTS`
- Safe for repeated execution

### Validation:

- Checks database connectivity
- Validates SQL syntax
- Tracks execution status

## 📋 **Deployment Checklist**

### Before Deployment:

- [ ] Test migrations locally
- [ ] Backup production database
- [ ] Commit all changes
- [ ] Update production URL in script

### After Deployment:

- [ ] Verify main website works
- [ ] Check admin panel accessibility
- [ ] Run migrations manually if needed
- [ ] Test user management
- [ ] Test product management
- [ ] Clear browser cache

## 🔍 **Troubleshooting**

### Common Issues:

#### 1. Migration System Not Accessible

**Problem**: Cannot access `/database/migrations.php`
**Solution**:

- Check file permissions
- Verify database connection
- Check hosting provider settings

#### 2. Migration Fails

**Problem**: Migration shows "❌ Failed"
**Solution**:

- Check error message in migration dashboard
- Verify database user permissions
- Check SQL syntax

#### 3. Tables Still Missing

**Problem**: Tables not created after migration
**Solution**:

- Run migrations manually
- Check database user has CREATE privileges
- Verify foreign key constraints

### Debug Commands:

```bash
# Check migration status
curl -s https://your-domain.com/database/migrations.php

# Run migrations manually
curl -X POST -d "run_migrations=1" https://your-domain.com/database/migrations.php

# Check database connection
php -r "require 'includes/db_connect.php'; echo 'Connected';"
```

## 📚 **Best Practices**

### Development Workflow:

1. **Make database changes locally**
2. **Add migration to the system**
3. **Test migration locally**
4. **Commit and push changes**
5. **Run deployment script**
6. **Verify production functionality**

### Migration Guidelines:

- **Always use `IF NOT EXISTS`** for table creation
- **Use `ADD COLUMN IF NOT EXISTS`** for column addition
- **Include clear descriptions** for each migration
- **Test migrations** before deployment
- **Backup database** before major changes

### Security Considerations:

- **Limit database user permissions** to necessary operations
- **Use prepared statements** for data insertion
- **Validate all inputs** in migration scripts
- **Log all migration activities** for audit trail

## 🎯 **Quick Start**

### For New Deployments:

1. Upload all files to production
2. Visit: `https://your-domain.com/database/migrations.php`
3. Click "🔄 Run Pending Migrations"
4. Test the application

### For Existing Deployments:

1. Run: `./deploy_with_migrations.sh`
2. Follow the post-deployment checklist
3. Test all functionality

## 📞 **Support**

If you encounter issues:

1. Check the migration dashboard for error messages
2. Review the troubleshooting section
3. Test database connectivity
4. Verify file permissions
5. Check hosting provider logs

---

**Remember**: Always backup your database before running migrations in production!
