<?php
// NexusLedger - Authentication Functions

session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function current_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? 'Guest',
        'full_name' => $_SESSION['full_name'] ?? 'Guest User',
        'role' => $_SESSION['role'] ?? 'user',
        'balance' => $_SESSION['balance'] ?? 0,
    ];
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header('Location: dashboard.php?error=' . urlencode('Access denied'));
        exit;
    }
}

function login_user($username, $password) {
    global $mysqli;

    $username = db_escape($username);
    $password_hash = md5($password);

    // SQL Injection here for LOW level
    if (SECURITY_LEVEL === 'low') {
        $query = "SELECT * FROM users WHERE username='$username' AND password='$password_hash' LIMIT 1";
        $result = $mysqli->query($query);
    } elseif (SECURITY_LEVEL === 'medium') {
        $username = str_replace(["'", '"', '--', '#', ';'], '', $username);
        $query = "SELECT * FROM users WHERE username='$username' AND password='$password_hash' LIMIT 1";
        $result = $mysqli->query($query);
    } elseif (SECURITY_LEVEL === 'high') {
        $query = "SELECT * FROM users WHERE username='" . db_escape($username) . "' AND password='$password_hash' LIMIT 1";
        $result = $mysqli->query($query);
    } else {
        // Impossible - prepared statement
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username=? AND password=? LIMIT 1");
        $stmt->execute([$username, $password_hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            setup_session($row);
            return true;
        }
        return false;
    }

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        setup_session($row);
        return true;
    }
    return false;
}

function setup_session($user) {
    // Weak Session ID for low level
    if (SECURITY_LEVEL === 'low') {
        $_SESSION['session_id'] = md5($user['id'] . time()); // predictable pattern
    } else {
        $_SESSION['session_id'] = bin2hex(random_bytes(32));
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['balance'] = $user['balance'];
    $_SESSION['currency'] = $user['currency'];
}

function logout_user() {
    session_destroy();
    header('Location: index.php');
    exit;
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}
