<?php
/**
 * Production Configuration File
 * This file contains production-specific settings for GT Automotives
 * Use this when .env files are not available on the production server
 */

// Production Database Configuration
define('PROD_DB_HOST', 'localhost');
define('PROD_DB_NAME', 'gt_automotives');
define('PROD_DB_USER', 'gtadmin');
define('PROD_DB_PASS', 'Vishal@1234#');
define('PROD_DB_PORT', '3306');

// Production Application Settings
define('PROD_APP_ENV', 'production');
define('PROD_APP_DEBUG', false);
define('PROD_APP_URL', 'https://www.gt-automotives.com');

// Production Session Settings
define('PROD_SESSION_LIFETIME', 120);
define('PROD_SESSION_SECURE', true);
define('PROD_SESSION_HTTPONLY', true);

// Production Error Reporting
define('PROD_ERROR_REPORTING', E_ALL & ~E_DEPRECATED & ~E_STRICT);
define('PROD_DISPLAY_ERRORS', false);
define('PROD_LOG_ERRORS', true);

// Production File Paths
define('PROD_BASE_PATH', '/opt/bitnami/apache/htdocs');
define('PROD_UPLOAD_PATH', PROD_BASE_PATH . '/uploads');
define('PROD_SESSION_PATH', '/opt/bitnami/php/var/run/session');

// Production Security Settings
define('PROD_HTTPS_REQUIRED', true);
define('PROD_SESSION_REGENERATE', true);
define('PROD_CSRF_PROTECTION', true);

// Production Cache Settings
define('PROD_CACHE_DRIVER', 'file');
define('PROD_CACHE_PATH', PROD_BASE_PATH . '/storage/cache');

// Production Mail Settings
define('PROD_MAIL_DRIVER', 'smtp');
define('PROD_MAIL_HOST', 'localhost');
define('PROD_MAIL_PORT', '25');
define('PROD_MAIL_ENCRYPTION', null);

// Production Logging
define('PROD_LOG_CHANNEL', 'stack');
define('PROD_LOG_LEVEL', 'error');
define('PROD_LOG_PATH', PROD_BASE_PATH . '/storage/logs');

// Check if we're in production environment
function isProduction() {
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    return $server_name === 'www.gt-automotives.com' || 
           $server_name === 'gt-automotives.com' ||
           strpos($server_name, 'gt-automotives.com') !== false;
}

// Get production configuration value
function getProductionConfig($key, $default = null) {
    $config_key = 'PROD_' . strtoupper($key);
    return defined($config_key) ? constant($config_key) : $default;
}

// Set production error reporting
if (isProduction()) {
    error_reporting(PROD_ERROR_REPORTING);
    ini_set('display_errors', PROD_DISPLAY_ERRORS ? '1' : '0');
    ini_set('log_errors', PROD_LOG_ERRORS ? '1' : '0');
    
    // Set session path for production
    if (is_dir(PROD_SESSION_PATH)) {
        ini_set('session.save_path', PROD_SESSION_PATH);
    }
}
?> 