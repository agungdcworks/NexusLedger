#!/bin/bash
#
# NexusLedger Uninstall Script
# Removes NexusLedger files and optionally the LAMP stack
# Run as root: sudo bash uninstall.sh
#

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
NC='\033[0m'

info()  { echo -e "${CYAN}[INFO]${NC} $1"; }
success() { echo -e "${GREEN}[OK]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }

if [[ $EUID -ne 0 ]]; then
    echo -e "${RED}[ERROR]${NC} Run as root: sudo bash uninstall.sh"
    exit 1
fi

echo ""
echo -e "${RED}============================================${NC}"
echo -e "${RED}     NexusLedger Uninstall Script${NC}"
echo -e "${RED}============================================${NC}"
echo ""

# Remove NexusLedger files
if [ -d "/var/www/html/NexusLedger" ]; then
    info "Removing NexusLedger from /var/www/html/NexusLedger..."
    rm -rf /var/www/html/NexusLedger
    success "NexusLedger files removed."
else
    info "NexusLedger directory not found. Skipping."
fi

# Remove uploads if orphaned
if [ -d "/var/www/html/NexusLedger" ]; then
    rm -rf /var/www/html/NexusLedger
fi

# Ask about database
echo ""
read -p "Drop the NexusLedger MySQL database and user? [y/N]: " DROP_DB
if [[ "$DROP_DB" =~ ^[Yy]$ ]]; then
    info "Dropping NexusLedger database and user..."

    mysql -u root -e "DROP DATABASE IF EXISTS nexusledger; DROP USER IF EXISTS 'nexus'@'127.0.0.1'; FLUSH PRIVILEGES;" 2>/dev/null ||
    mysql -u root -proot -e "DROP DATABASE IF EXISTS nexusledger; DROP USER IF EXISTS 'nexus'@'127.0.0.1'; FLUSH PRIVILEGES;" 2>/dev/null ||
    mysql -u root --socket=/run/mysqld/mysqld.sock -e "DROP DATABASE IF EXISTS nexusledger; DROP USER IF EXISTS 'nexus'@'127.0.0.1'; FLUSH PRIVILEGES;" 2>/dev/null ||
    warn "Could not auto-drop database. Run manually:"
    echo "  mysql -u root"
    echo "  DROP DATABASE IF EXISTS nexusledger;"
    echo "  DROP USER IF EXISTS 'nexus'@'127.0.0.1';"
    echo "  FLUSH PRIVILEGES;"

    success "Database cleanup attempted."
fi

# Ask about LAMP stack
echo ""
echo -n "Remove Apache, MySQL, PHP and all dependencies? [y/N]: "
read REMOVE_LAMP
if [[ "$REMOVE_LAMP" =~ ^[Yy]$ ]]; then
    info "Stopping services..."
    systemctl stop apache2 2>/dev/null || true
    systemctl stop mariadb 2>/dev/null || true
    systemctl stop mysql 2>/dev/null || true

    info "Removing LAMP stack packages..."
    apt-get remove --purge -y -qq \
        apache2 apache2-bin apache2-data apache2-utils \
        mariadb-server mariadb-client mariadb-common \
        php php-mysql php-mysqli php-gd php-xml php-mbstring \
        php-curl php-pdo-mysql libapache2-mod-php 2>/dev/null
    apt-get autoremove --purge -y -qq 2>/dev/null
    success "LAMP stack removed."
fi

# Restart Apache if still present
if systemctl is-active --quiet apache2 2>/dev/null; then
    systemctl restart apache2
fi

echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}       Uninstall Complete${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo " Summary:"
echo "   - NexusLedger files : removed"
[[ "$DROP_DB" =~ ^[Yy]$ ]] && echo "   - Database (nexusledger) : dropped" || echo "   - Database (nexusledger) : kept"
[[ "$REMOVE_LAMP" =~ ^[Yy]$ ]] && echo "   - LAMP stack             : removed" || echo "   - LAMP stack             : kept"
echo ""
