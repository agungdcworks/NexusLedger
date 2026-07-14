<?php
// NexusLedger - Financial Portfolio Management
// Database Configuration

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'nexusledger');
define('DB_USER', 'nexus');
define('DB_PASS', 'n3xus!f1n@nce');

define('SITE_NAME', 'NexusLedger');
define('SITE_URL', '/NexusLedger');

// Security level: low, medium, high, impossible
// Reads from cookie if set, falls back to 'low'
$security_level = 'low';
if (isset($_COOKIE['security_level'])) {
    $allowed_levels = ['low', 'medium', 'high', 'impossible'];
    if (in_array($_COOKIE['security_level'], $allowed_levels)) {
        $security_level = $_COOKIE['security_level'];
    }
}
define('SECURITY_LEVEL', $security_level);

// Session configuration
ini_set('session.cookie_httponly', 0);
ini_set('session.cookie_samesite', '');
