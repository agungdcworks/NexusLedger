<?php
// NexusLedger - Database Setup
$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

function setup_db_connection() {
    $mysqli = @new mysqli('127.0.0.1', 'nexus', 'n3xus!f1n@nce', 'nexusledger', '3306');
    if (!$mysqli->connect_error) {
        return $mysqli;
    }
    // Try creating the database
    $mysqli = @new mysqli('127.0.0.1', 'root', '', null, '3306');
    if ($mysqli->connect_error) {
        $mysqli = @new mysqli('127.0.0.1', 'root', 'root', null, '3306');
    }
    return $mysqli;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $mysqli = setup_db_connection();
    if ($mysqli->connect_error) {
        $error = 'Cannot connect to MySQL: ' . $mysqli->connect_error;
    } else {
        $sql_file = __DIR__ . '/database/schema.sql';
        if (!file_exists($sql_file)) {
            $error = 'Schema file not found at: ' . $sql_file;
        } else {
            $sql = file_get_contents($sql_file);
            // Split by semicolons, respecting newlines
            $queries = array_filter(
                array_map('trim',
                    explode(";\n", $sql)
                ),
                function($q) { return !empty($q) && !preg_match('/^--|^\/\*/', $q); }
            );

            $errors = [];
            foreach ($queries as $query) {
                if (!$mysqli->query($query)) {
                    $errors[] = $mysqli->error . ' (in: ' . substr($query, 0, 80) . '...)';
                }
            }

            if (empty($errors)) {
                // Create the nexus user if using root
                $mysqli->query("CREATE USER IF NOT EXISTS 'nexus'@'127.0.0.1' IDENTIFIED BY 'n3xus!f1n@nce'");
                $mysqli->query("GRANT ALL PRIVILEGES ON nexusledger.* TO 'nexus'@'127.0.0.1'");
                $mysqli->query("FLUSH PRIVILEGES");
                $success = 'Database setup complete! All tables created and sample data inserted.';
            } else {
                $error = "Errors during setup:\n" . implode("\n", array_slice($errors, 0, 5));
                $success = 'Partial setup may have completed. Some tables may already exist (that\'s OK).';
            }
        }
    }
}

// Check current status
$db_ok = false;
$test_mysqli = @new mysqli('127.0.0.1', 'nexus', 'n3xus!f1n@nce', 'nexusledger', '3306');
if (!$test_mysqli->connect_error) {
    $result = $test_mysqli->query("SHOW TABLES");
    $db_ok = ($result && $result->num_rows >= 6);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup | NexusLedger</title>
    <link rel="stylesheet" href="/NexusLedger/assets/css/main.css">
    <style>
    body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg-root)}
    body::before{animation:gridFlow 25s linear infinite}
    .setup-wrapper{position:relative;z-index:1;width:100%;max-width:600px;padding:24px}
    .setup-card{background:rgba(17,22,32,0.88);backdrop-filter:blur(28px);-webkit-backdrop-filter:blur(28px);border:1px solid var(--border);border-radius:var(--radius-lg);padding:44px 38px;box-shadow:0 20px 60px rgba(0,0,0,0.55)}
    .setup-card h2{font-size:1.3rem;color:var(--text);margin:0 0 8px;border:none;padding:0}
    .setup-card h2::after{display:none}
    .step-indicator{display:flex;gap:12px;margin-bottom:30px}
    .step-dot{width:12px;height:12px;border-radius:50%;background:var(--border)}
    .step-dot.active{background:var(--gold);box-shadow:0 0 10px var(--gold-glow)}
    .step-dot.done{background:var(--green)}
    .alert{margin-bottom:20px}
    .btn-setup{padding:13px 28px;border-radius:var(--radius);font-size:14px;font-weight:700;cursor:pointer;border:none;font-family:'Inter',sans-serif;transition:var(--anim)}
    .btn-setup.primary{background:linear-gradient(135deg,#b8943a,#c9a454);color:#0a0c14}
    .btn-setup.primary:hover{box-shadow:0 0 28px var(--gold-glow)}
    .btn-setup.success{background:linear-gradient(135deg,#27ae60,#2ecc71);color:#fff}
    .db-info{font-size:13px;color:var(--text-muted);margin-top:16px;padding:12px;background:rgba(255,255,255,0.02);border-radius:var(--radius);font-family:monospace}
    .db-info span{color:var(--gold)}
    pre.errors{background:rgba(231,76,60,0.08);border:1px solid rgba(231,76,60,0.2);padding:12px;border-radius:var(--radius);font-size:11px;color:var(--red);overflow-x:auto;max-height:200px;overflow-y:auto}
    </style>
</head>
<body class="dark">
<div class="setup-wrapper">
    <div class="setup-card">
        <div style="text-align:center;margin-bottom:24px">
            <span style="font-size:36px;color:var(--gold);display:block;margin-bottom:8px">&#9670;</span>
            <h2>Nexus<span style="color:var(--gold)">Ledger</span></h2>
            <p style="color:var(--text-muted);font-size:13px">Database Configuration</p>
        </div>

        <div class="step-indicator" style="justify-content:center">
            <div class="step-dot active"></div>
            <div class="step-dot <?php echo $db_ok ? 'done' : ''; ?>"></div>
            <div class="step-dot <?php echo $db_ok ? 'done' : ''; ?>"></div>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <strong>Error:</strong>
            <?php if (strpos($error, "\n") !== false): ?>
            <pre class="errors"><?php echo htmlspecialchars($error); ?></pre>
            <?php else: ?>
            <?php echo htmlspecialchars($error); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($db_ok): ?>
            <div class="alert alert-success">
                <strong>Database is ready!</strong> All tables exist.
            </div>
            <div style="text-align:center;margin-top:16px">
                <a href="/NexusLedger/index.php" class="btn-setup success">Go to Login</a>
            </div>
        <?php else: ?>
            <div class="db-info">
                <p><span>Host:</span> 127.0.0.1:3306</p>
                <p><span>Database:</span> nexusledger</p>
                <p><span>User:</span> nexus</p>
                <p style="margin-top:8px;color:var(--text-muted)">The setup will create the database, tables, and sample data.</p>
            </div>
            <form method="POST" style="text-align:center;margin-top:20px">
                <button type="submit" name="action" value="setup" class="btn-setup primary">Create / Reset Database</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
