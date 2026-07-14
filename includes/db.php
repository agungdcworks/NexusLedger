<?php
// NexusLedger - Database Connection

require_once __DIR__ . '/../config/config.php';

$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($mysqli->connect_error) {
    die('<div style="background:#1a1d23;color:#f44d4d;padding:40px;text-align:center;font-family:sans-serif;">
        <h2>Database Connection Error</h2>
        <p>Unable to connect to the financial database.</p>
        <p><small>' . $mysqli->connect_error . '</small></p>
        <p><a href="setup.php" style="color:#4d8af0;">Run Setup</a></p>
    </div>');
}

// PDO connection for prepared statements (impossible level)
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    $pdo = null;
}

function db_escape($value) {
    global $mysqli;
    return $mysqli->real_escape_string($value);
}
