<?php
// NexusLedger - Page Template

require_once __DIR__ . '/auth.php';

function page_header($title = 'Dashboard', $page_id = 'dashboard') {
    $user = current_user();
    $theme = $_COOKIE['theme'] ?? 'dark';
    $base = '/NexusLedger';

    $nav = [
        'main' => [
            ['id'=>'dashboard', 'label'=>'Dashboard', 'icon'=>'chart', 'url'=>"$base/dashboard.php"],
            ['id'=>'transactions', 'label'=>'Transactions', 'icon'=>'list', 'url'=>"$base/transactions.php"],
            ['id'=>'transfer', 'label'=>'Transfer Funds', 'icon'=>'send', 'url'=>"$base/transfer.php"],
            ['id'=>'documents', 'label'=>'Documents', 'icon'=>'file', 'url'=>"$base/documents.php"],
            ['id'=>'reports', 'label'=>'Reports', 'icon'=>'report', 'url'=>"$base/reports.php"],
            ['id'=>'api', 'label'=>'API Access', 'icon'=>'code', 'url'=>"$base/api/index.php"],
        ],
        'account' => [
            ['id'=>'profile', 'label'=>'My Profile', 'icon'=>'user', 'url'=>"$base/profile.php"],
            ['id'=>'security', 'label'=>'Security', 'icon'=>'lock', 'url'=>"$base/security.php"],
        ],
    ];

    if (is_admin()) {
        $nav['admin'] = [
            ['id'=>'admin_users', 'label'=>'User Management', 'icon'=>'users', 'url'=>"$base/admin/users.php"],
            ['id'=>'admin_audit', 'label'=>'Audit Logs', 'icon'=>'log', 'url'=>"$base/admin/audit.php"],
            ['id'=>'admin_api', 'label'=>'API Dashboard', 'icon'=>'api', 'url'=>"$base/admin/api.php"],
        ];
    }

    $nav['logout'] = [
        ['id'=>'logout', 'label'=>'Sign Out', 'icon'=>'logout', 'url'=>"$base/logout.php"],
    ];

    // Build sidebar nav HTML
    $nav_html = '';
    $section_labels = ['main'=>'Portfolio', 'admin'=>'Administration', 'account'=>'Settings', 'logout'=>'Session'];
    foreach ($nav as $section_id => $items) {
        $label = $section_labels[$section_id] ?? '';
        $nav_html .= '<div class="nav-section">';
        if ($label) $nav_html .= '<span class="nav-label">' . $label . '</span>';
        $nav_html .= '<ul class="nav-list">';
        foreach ($items as $item) {
            $active = ($item['id'] === $page_id) ? ' active' : '';
            $nav_html .= '<li class="nav-item' . $active . '">
                <a href="' . $item['url'] . '">
                    <span class="nav-icon">' . svg_icon($item['icon']) . '</span>
                    <span>' . $item['label'] . '</span>
                </a>
            </li>';
        }
        $nav_html .= '</ul></div>';
    }

    echo '<!DOCTYPE html>
<html lang="en" data-theme="' . $theme . '">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . ' | ' . SITE_NAME . '</title>
    <link rel="stylesheet" href="' . $base . '/assets/css/main.css">
    <link rel="icon" type="image/svg+xml" href="' . $base . '/assets/images/icon.svg">
    <script src="' . $base . '/assets/js/app.js" defer></script>
</head>
<body class="' . $theme . '">
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="' . $base . '/dashboard.php" class="brand">
                <img src="' . $base . '/assets/images/logo.png" alt="NexusLedger" class="brand-logo">
            </a>
        </div>
        <nav class="sidebar-nav">' . $nav_html . '</nav>
        <div class="sidebar-footer">
            <button class="theme-btn" onclick="toggleTheme()" title="Toggle Theme">
                <span class="nav-icon">' . svg_icon('theme') . '</span>
            </button>
            <div class="user-mini">
                <div class="user-avatar">' . strtoupper(substr($user['full_name'], 0, 2)) . '</div>
                <div class="user-detail">
                    <span class="user-name">' . htmlspecialchars($user['full_name']) . '</span>
                    <span class="user-role">' . ucfirst($user['role']) . '</span>
                </div>
            </div>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <h2 class="page-title">' . htmlspecialchars($title) . '</h2>
            </div>
            <div class="topbar-right">
                <div class="security-switcher">
                    <span class="sec-label">Security</span>
                    <select class="sec-select" onchange="changeSecurityLevel(this.value)" title="Change security level">
                        <option value="low" ' . (SECURITY_LEVEL === 'low' ? 'selected' : '') . '>Low</option>
                        <option value="medium" ' . (SECURITY_LEVEL === 'medium' ? 'selected' : '') . '>Medium</option>
                        <option value="high" ' . (SECURITY_LEVEL === 'high' ? 'selected' : '') . '>High</option>
                        <option value="impossible" ' . (SECURITY_LEVEL === 'impossible' ? 'selected' : '') . '>Impossible</option>
                    </select>
                    <span class="sec-badge sec-' . SECURITY_LEVEL . '">' . ucfirst(SECURITY_LEVEL) . '</span>
                </div>
                <div class="balance-badge">
                    <span class="balance-label">Balance</span>
                    <span class="balance-value">$' . number_format($user['balance'], 2) . '</span>
                </div>
                <div class="notif-bell">' . svg_icon('bell') . '<span class="notif-dot"></span></div>
            </div>
        </header>
        <div class="content">';
}

function page_footer() {
    echo '    </div>
        <footer class="footer">
            <p>&copy; ' . date('Y') . ' NexusLedger Financial Systems. All rights reserved. | SOC 2 Type II Certified</p>
        </footer>
    </main>
</div>
</body>
</html>';
}

function svg_icon($name) {
    $icons = [
        'chart'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 16l4-8 4 4 4-6"/></svg>',
        'list'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
        'send'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9"/></svg>',
        'file'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
        'report'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
        'code'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
        'user'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'lock'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
        'users'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'log'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>',
        'api'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>',
        'logout'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
        'bell'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
        'theme'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>',
        'search'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
        'filter'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>',
        'plus'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
        'download' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
        'eye'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
    ];
    return $icons[$name] ?? '';
}
