<?php
// NexusLedger - Login Page
// Contains: SQL Injection (login bypass), Brute Force (no rate limiting)

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login_user($username, $password)) {
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid credentials. Please try again.';

    // No brute force protection in low/medium
}

$redirect = $_GET['redirect'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | NexusLedger Financial</title>
    <link rel="stylesheet" href="/NexusLedger/assets/css/main.css">
    <style>
    body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg-root)}
    body::before{animation:gridFlow 25s linear infinite}
    body::after{animation:orbFloat 14s ease-in-out infinite alternate}
    .login-wrapper{position:relative;z-index:1;width:100%;max-width:420px;padding:24px}
    .login-card{background:rgba(17,22,32,0.85);backdrop-filter:blur(28px);-webkit-backdrop-filter:blur(28px);border:1px solid var(--border);border-radius:var(--radius-lg);padding:44px 38px;box-shadow:0 20px 60px rgba(0,0,0,0.55),0 0 40px rgba(201,164,84,0.05);position:relative;overflow:hidden;animation:cardIn 0.7s ease-out}
    .login-card::before{content:'';position:absolute;top:0;left:28px;right:28px;height:1px;background:linear-gradient(90deg,transparent,var(--gold),transparent);opacity:0.4}
    @keyframes cardIn{from{opacity:0;transform:translateY(25px) scale(0.97)}to{opacity:1;transform:translateY(0) scale(1)}}
    .login-brand{text-align:center;margin-bottom:36px}
    .login-brand .brand-icon{font-size:36px;color:var(--gold);display:block;margin-bottom:8px;filter:drop-shadow(0 0 14px var(--gold-glow));animation:pulse 3s ease-in-out infinite alternate}
    .login-logo{width:100%;max-width:320px;height:auto;display:block;margin:0 auto 12px;filter:drop-shadow(0 0 16px rgba(201,164,84,0.25))}
    @keyframes pulse{0%{filter:drop-shadow(0 0 8px rgba(201,164,84,0.3))}100%{filter:drop-shadow(0 0 22px rgba(201,164,84,0.6))}}
    .login-brand h1{font-size:1.5rem;font-weight:700;color:var(--text);border:none;padding:0;margin:0}
    .login-brand h1::after{display:none}
    .login-brand p{font-size:13px;color:var(--text-muted);margin-top:4px}
    .form-group{margin-bottom:20px}
    .form-label{display:block;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);margin-bottom:6px}
    .form-input{width:100%;padding:13px 15px;font-size:14px;background:var(--bg-input);border:1px solid var(--border);color:var(--text);border-radius:var(--radius);transition:var(--anim);outline:none;font-family:'Inter',sans-serif}
    .form-input:focus{border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,164,84,0.1),0 0 18px var(--gold-glow)}
    .form-input::placeholder{color:var(--text-muted)}
    .btn-login{width:100%;padding:13px;background:linear-gradient(135deg,#b8943a,#c9a454);color:#0a0c14;border:none;border-radius:var(--radius);font-size:14px;font-weight:700;cursor:pointer;letter-spacing:0.03em;transition:var(--anim);font-family:'Inter',sans-serif;margin-top:6px}
    .btn-login:hover{background:linear-gradient(135deg,#c9a454,#dbb860);box-shadow:0 0 30px var(--gold-glow),0 6px 22px rgba(201,164,84,0.3);transform:translateY(-2px)}
    .login-error{background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.25);padding:12px 16px;border-radius:var(--radius);color:var(--red);font-size:13px;text-align:center;margin-bottom:20px;animation:shake 0.45s}
    @keyframes shake{0%,100%{transform:translateX(0)}15%{transform:translateX(-8px)}30%{transform:translateX(8px)}45%{transform:translateX(-5px)}60%{transform:translateX(5px)}75%{transform:translateX(-2px)}90%{transform:translateX(2px)}}
    .login-footer{text-align:center;margin-top:22px;font-size:12px;color:var(--text-muted)}
    .login-footer a{color:var(--text-muted)}
    .login-footer a:hover{color:var(--gold)}
    </style>
</head>
<body class="dark">
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-brand">
            <img src="/NexusLedger/assets/images/logo.png" alt="NexusLedger" class="login-logo">
            <p>Financial Portfolio Management</p>
        </div>
        <?php if ($error): ?>
        <div class="login-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" placeholder="Enter your username" autofocus required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
            </div>
            <?php if ($redirect): ?>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
            <?php endif; ?>
            <button type="submit" class="btn-login">Sign In</button>
        </form>
    </div>
    <div class="login-footer">
        <p>&copy; <?php echo date('Y'); ?> NexusLedger Financial Systems. SOC 2 Type II Certified.</p>
        <div style="margin-top:14px;display:flex;align-items:center;justify-content:center;gap:8px">
            <span style="font-size:10px;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted)">Security Level:</span>
            <select onchange="document.cookie='security_level='+this.value+';path=/';location.reload()"
                style="background:var(--bg-input);border:1px solid var(--border);color:var(--text);font-size:11px;padding:3px 7px;border-radius:4px;cursor:pointer;font-family:'Inter',sans-serif">
                <option value="low" <?php echo SECURITY_LEVEL==='low'?'selected':''; ?>>Low</option>
                <option value="medium" <?php echo SECURITY_LEVEL==='medium'?'selected':''; ?>>Medium</option>
                <option value="high" <?php echo SECURITY_LEVEL==='high'?'selected':''; ?>>High</option>
                <option value="impossible" <?php echo SECURITY_LEVEL==='impossible'?'selected':''; ?>>Impossible</option>
            </select>
            <span class="sec-badge sec-<?php echo SECURITY_LEVEL; ?>" style="display:inline-block;vertical-align:middle"><?php echo ucfirst(SECURITY_LEVEL); ?></span>
        </div>
    </div>
</div>
</body>
</html>
