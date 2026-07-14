#!/bin/bash
#
# NexusLedger - Quick Start Script
# Use after VM reboot to bring services back up
# Run as root: sudo bash start.sh
#

RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
NC='\033[0m'
BOLD='\033[1m'

banner() {
    echo -e "${GREEN}"
    echo "  ┌──────────────────────────────────────────┐"
    echo "  │     NexusLedger - Startup Script          │"
    echo "  └──────────────────────────────────────────┘"
    echo -e "${NC}"
}

ok() { echo -e "  ${GREEN}[OK]${NC} $1"; }
fail() { echo -e "  ${RED}[FAIL]${NC} $1"; }
info() { echo -e "  ${CYAN}[..]${NC} $1"; }

if [[ $EUID -ne 0 ]]; then
    echo -e "${RED}Run as root: sudo bash start.sh${NC}"
    echo -e "${YELLOW}Or to enable auto-start on boot:${NC}"
    echo "  sudo systemctl enable apache2 mariadb"
    exit 1
fi

banner

# Start MySQL / MariaDB
info "Starting database service..."
if systemctl start mariadb 2>/dev/null; then
    ok "MariaDB started"
elif systemctl start mysql 2>/dev/null; then
    ok "MySQL started"
else
    fail "Could not start database service"
fi

# Start Apache
info "Starting web server..."
if systemctl start apache2 2>/dev/null; then
    ok "Apache2 started"
else
    fail "Could not start Apache2"
fi

# Enable services to auto-start on next boot
info "Enabling auto-start on boot..."
systemctl enable mariadb 2>/dev/null && ok "MariaDB auto-start enabled" || systemctl enable mysql 2>/dev/null && ok "MySQL auto-start enabled" || fail "Database auto-start"
systemctl enable apache2 2>/dev/null && ok "Apache2 auto-start enabled" || fail "Apache2 auto-start"

# Verify
echo ""
echo -e "${BOLD}  Service Status:${NC}"
echo "  ─────────────────────────────────────────"

if systemctl is-active --quiet mariadb 2>/dev/null || systemctl is-active --quiet mysql 2>/dev/null; then
    echo -e "  Database  : ${GREEN}RUNNING${NC}"
else
    echo -e "  Database  : ${RED}STOPPED${NC}"
fi

if systemctl is-active --quiet apache2 2>/dev/null; then
    echo -e "  Apache    : ${GREEN}RUNNING${NC}"
else
    echo -e "  Apache    : ${RED}STOPPED${NC}"
fi

# Check NexusLedger
if [ -d "/var/www/html/NexusLedger" ]; then
    echo -e "  NexLedger : ${GREEN}DEPLOYED${NC}"
else
    echo -e "  NexLedger : ${RED}NOT FOUND${NC}"
fi

echo ""
ip=$(hostname -I | awk '{print $1}')
echo -e "  ${BOLD}Open:${NC} ${CYAN}http://${ip}/NexusLedger${NC}"
echo -e "  ${BOLD}Login:${NC} admin / admin123"
echo ""
