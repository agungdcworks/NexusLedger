// NexusLedger - Application JavaScript

function changeSecurityLevel(level) {
    document.cookie = 'security_level=' + level + ';path=/;max-age=31536000';
    location.reload();
}

function toggleTheme() {
    const html = document.documentElement;
    const body = document.body;
    const current = body.classList.contains('dark') ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    body.classList.remove(current);
    body.classList.add(next);
    html.setAttribute('data-theme', next);
    document.cookie = 'theme=' + next + ';path=/;max-age=31536000';
}

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    // Active nav item
    const path = window.location.pathname;
    document.querySelectorAll('.nav-item a').forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && path.includes(href.split('/').pop().replace(/\?.+/, ''))) {
            link.parentElement.classList.add('active');
        }
    });

    // Flash messages auto-dismiss
    document.querySelectorAll('.alert').forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            alert.style.transition = '0.4s ease';
            setTimeout(function() { if (alert.parentNode) alert.remove(); }, 400);
        }, 5000);
    });
});

// Modal helpers
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'flex';
}
function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.style.display = 'none';
}

// Confirm dialog
function confirmAction(msg) {
    return confirm(msg || 'Are you sure?');
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard!');
    });
}
