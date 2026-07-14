#!/bin/bash
#
# NexusLedger - Financial Portfolio Management Dashboard
# One-Click Installation Script
# For Debian/Ubuntu-based systems (Kali, Ubuntu, Debian)
# Run as root: sudo bash install.sh
#

set -e

# === Color Output ===
RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
NC='\033[0m'
BOLD='\033[1m'

banner() {
    echo -e "${GREEN}"
    echo "╔══════════════════════════════════════════════════════════╗"
    echo "║                                                          ║"
    echo "║   ███╗   ██╗███████╗██╗  ██╗██╗   ██╗███████╗            ║"
    echo "║   ████╗  ██║██╔════╝╚██╗██╔╝██║   ██║██╔════╝            ║"
    echo "║   ██╔██╗ ██║█████╗   ╚███╔╝ ██║   ██║███████╗            ║"
    echo "║   ██║╚██╗██║██╔══╝   ██╔██╗ ██║   ██║╚════██║            ║"
    echo "║   ██║ ╚████║███████╗██╔╝ ██╗╚██████╔╝███████║            ║"
    echo "║   ╚═╝  ╚═══╝╚══════╝╚═╝  ╚═╝ ╚═════╝ ╚══════╝            ║"
    echo "║                                                          ║"
    echo "║          Financial Portfolio Management System           ║"
    echo "║                  One-Click Installer                      ║"
    echo "║                                                          ║"
    echo "╚══════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

info()  { echo -e "${CYAN}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[OK]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

check_root() {
    if [[ $EUID -ne 0 ]]; then
        error "This script must be run as root. Use: sudo bash install.sh"
    fi
}

detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
        VER=$VERSION_ID
    else
        error "Cannot detect OS. This script supports Debian/Ubuntu/Kali."
    fi

    case $OS in
        ubuntu|debian|kali|linuxmint|parrot)
            success "Detected OS: $OS $VER"
            ;;
        *)
            warn "Untested OS: $OS. Proceeding anyway..."
            ;;
    esac
}

install_lamp() {
    info "Updating package list..."
    apt-get update -qq

    info "Installing Apache2..."
    apt-get install -y -qq apache2

    info "Installing MariaDB (MySQL)..."
    apt-get install -y -qq mariadb-server mariadb-client

    info "Installing PHP and required modules..."
    apt-get install -y -qq \
        php \
        php-mysql \
        php-mysqli \
        php-gd \
        php-xml \
        php-mbstring \
        php-curl \
        php-pdo-mysql \
        libapache2-mod-php

    success "LAMP stack installed."
}

configure_php() {
    info "Configuring PHP..."

    PHP_INI="/etc/php/$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')/apache2/php.ini"

    if [ ! -f "$PHP_INI" ]; then
        PHP_INI=$(php -r 'echo php_ini_loaded_file();' 2>/dev/null)
    fi

    if [ -f "$PHP_INI" ]; then
        sed -i 's/^allow_url_fopen = .*/allow_url_fopen = On/' "$PHP_INI"
        sed -i 's/^allow_url_include = .*/allow_url_include = On/' "$PHP_INI"
        sed -i 's/^display_errors = .*/display_errors = On/' "$PHP_INI"
        sed -i 's/^display_startup_errors = .*/display_startup_errors = On/' "$PHP_INI"
        success "PHP configuration updated: $PHP_INI"
    else
        warn "Could not find php.ini. Please manually enable: allow_url_fopen, allow_url_include, display_errors"
    fi
}

configure_mysql() {
    info "Configuring MariaDB/MySQL..."

    systemctl start mariadb 2>/dev/null || systemctl start mysql 2>/dev/null || true
    systemctl enable mariadb 2>/dev/null || systemctl enable mysql 2>/dev/null || true

    info "Creating NexusLedger database and user..."
    local mysql_done=false

    for attempt in "root:empty" "root:root" "root:socket"; do
        case $attempt in
            "root:empty")
                if mysql -u root -e "SELECT 1" 2>/dev/null; then
                    mysql -u root <<'EOSQL'
CREATE DATABASE IF NOT EXISTS nexusledger CHARACTER SET utf8mb4;
CREATE USER IF NOT EXISTS 'nexus'@'127.0.0.1' IDENTIFIED BY 'n3xus!f1n@nce';
GRANT ALL PRIVILEGES ON nexusledger.* TO 'nexus'@'127.0.0.1';
FLUSH PRIVILEGES;
EOSQL
                    mysql_done=true
                    break
                fi
                ;;
            "root:root")
                if mysql -u root -proot -e "SELECT 1" 2>/dev/null; then
                    mysql -u root -proot <<'EOSQL'
CREATE DATABASE IF NOT EXISTS nexusledger CHARACTER SET utf8mb4;
CREATE USER IF NOT EXISTS 'nexus'@'127.0.0.1' IDENTIFIED BY 'n3xus!f1n@nce';
GRANT ALL PRIVILEGES ON nexusledger.* TO 'nexus'@'127.0.0.1';
FLUSH PRIVILEGES;
EOSQL
                    mysql_done=true
                    break
                fi
                ;;
            "root:socket")
                if mysql -u root --socket=/run/mysqld/mysqld.sock -e "SELECT 1" 2>/dev/null; then
                    mysql -u root --socket=/run/mysqld/mysqld.sock <<'EOSQL'
CREATE DATABASE IF NOT EXISTS nexusledger CHARACTER SET utf8mb4;
CREATE USER IF NOT EXISTS 'nexus'@'127.0.0.1' IDENTIFIED BY 'n3xus!f1n@nce';
GRANT ALL PRIVILEGES ON nexusledger.* TO 'nexus'@'127.0.0.1';
FLUSH PRIVILEGES;
EOSQL
                    mysql_done=true
                    break
                fi
                ;;
        esac
    done

    if $mysql_done; then
        success "MySQL configured: database 'nexusledger', user 'nexus'"
    else
        warn "Could not auto-configure MySQL. Please run manually:"
        echo "  sudo mysql -u root"
        echo "  CREATE DATABASE nexusledger;"
        echo "  CREATE USER 'nexus'@'127.0.0.1' IDENTIFIED BY 'n3xus!f1n@nce';"
        echo "  GRANT ALL ON nexusledger.* TO 'nexus'@'127.0.0.1';"
        echo "  FLUSH PRIVILEGES;"
    fi
}

deploy_app() {
    info "Deploying NexusLedger to /var/www/html/NexusLedger..."

    if [ -d "/var/www/html/NexusLedger" ]; then
        warn "Existing NexusLedger found. Removing..."
        rm -rf /var/www/html/NexusLedger
    fi

    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    cp -r "$SCRIPT_DIR" /var/www/html/NexusLedger

    chown -R www-data:www-data /var/www/html/NexusLedger
    chmod -R 755 /var/www/html/NexusLedger

    # Make uploads directory writable
    mkdir -p /var/www/html/NexusLedger/uploads
    chmod 777 /var/www/html/NexusLedger/uploads

    success "NexusLedger deployed to /var/www/html/NexusLedger"
}

init_database() {
    info "Initializing database schema..."

    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    local sql_file="$SCRIPT_DIR/database/schema.sql"

    if [ ! -f "$sql_file" ]; then
        sql_file="/var/www/html/NexusLedger/database/schema.sql"
    fi

    if [ -f "$sql_file" ]; then
        local mysql_ok=false
        for attempt in "nexus:pass" "root:empty" "root:root"; do
            case $attempt in
                "nexus:pass")
                    if mysql -u nexus -p'n3xus!f1n@nce' -h 127.0.0.1 -e "SELECT 1" 2>/dev/null; then
                        mysql -u nexus -p'n3xus!f1n@nce' -h 127.0.0.1 < "$sql_file" 2>/dev/null && mysql_ok=true && break
                    fi
                    ;;
                "root:empty")
                    if mysql -u root -e "SELECT 1" 2>/dev/null; then
                        mysql -u root < "$sql_file" 2>/dev/null && mysql_ok=true && break
                    fi
                    ;;
                "root:root")
                    if mysql -u root -proot -e "SELECT 1" 2>/dev/null; then
                        mysql -u root -proot < "$sql_file" 2>/dev/null && mysql_ok=true && break
                    fi
                    ;;
            esac
        done

        if $mysql_ok; then
            success "Database schema initialized successfully."
        else
            warn "Could not auto-initialize database. Please visit setup.php in browser."
        fi
    else
        warn "Schema file not found. Please run setup.php manually."
    fi
}

configure_apache() {
    info "Configuring Apache..."
    a2enmod rewrite 2>/dev/null || true
    systemctl enable apache2 2>/dev/null || true
    systemctl restart apache2
    success "Apache configured, auto-start enabled, and restarted."
}

print_summary() {
    local ip
    ip=$(hostname -I | awk '{print $1}')

    echo ""
    echo -e "${GREEN}${BOLD}╔══════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}${BOLD}║       NEXUSLEDGER INSTALLATION COMPLETE                   ║${NC}"
    echo -e "${GREEN}${BOLD}╚══════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "  ${BOLD}Application:${NC}  ${CYAN}http://${ip}/NexusLedger${NC}"
    echo -e "  ${BOLD}Setup:${NC}       ${CYAN}http://${ip}/NexusLedger/setup.php${NC}"
    echo ""
    echo -e "  ${BOLD}Default Credentials:${NC}"
    echo -e "  ┌────────────────────────────────────────────────────┐"
    echo -e "  │  admin       / admin123      (Administrator)        │"
    echo -e "  │  john.doe    / password123   (Manager)              │"
    echo -e "  │  jane.smith  / password123   (User)                 │"
    echo -e "  │  bob.wilson  / password123   (User)                 │"
    echo -e "  │  alice.brown / password123   (Manager)              │"
    echo -e "  └────────────────────────────────────────────────────┘"
    echo ""
    echo -e "  ${BOLD}API Key:${NC}      ${YELLOW}demo${NC} (test key for API access)"
    echo ""
    echo -e "  ${BOLD}Database:${NC}"
    echo -e "  Name: ${YELLOW}nexusledger${NC}  |  User: ${YELLOW}nexus${NC}  |  Pass: ${YELLOW}n3xus!f1n@nce${NC}"
    echo ""
    echo -e "  ${BOLD}Security Level:${NC} ${RED}LOW${NC} (change in config/config.php)"
    echo ""
    echo -e "  ${BOLD}Pages with vulnerabilities:${NC}"
    echo -e "  ├── Login        - SQL Injection, Brute Force"
    echo -e "  ├── Dashboard    - Broken Access Control, Weak Session ID"
    echo -e "  ├── Transactions - SQL Injection, XSS Reflected"
    echo -e "  ├── Transfer     - CSRF, Insecure CAPTCHA"
    echo -e "  ├── Documents    - Unrestricted File Upload"
    echo -e "  ├── Reports      - Command Injection, File Inclusion"
    echo -e "  ├── Profile      - XSS Stored, XSS DOM"
    echo -e "  ├── Security     - CSRF, Open Redirect, CSP Bypass, JS Attacks"
    echo -e "  ├── Admin/Users  - Broken Access Control, SQLi Blind"
    echo -e "  ├── Admin/Audit  - Broken Access Control"
    echo -e "  ├── Admin/API    - Broken Access Control"
    echo -e "  └── API          - Auth Bypass, IDOR, Mass Assignment"
    echo ""
    echo -e "  ${RED}${BOLD}WARNING: Intentionally vulnerable! Only run in isolated VM/CTF!${NC}"
    echo ""
}

# === Main Execution ===
main() {
    banner
    check_root
    detect_os

    echo ""
    info "Starting NexusLedger installation..."
    echo ""

    install_lamp
    configure_php
    configure_mysql
    deploy_app
    init_database
    configure_apache
    print_summary
}

main
