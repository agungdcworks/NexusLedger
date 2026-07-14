<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)">
    <img src="LogoNexusLedger.png" alt="NexusLedger" width="600">
  </picture>
</p>

<p align="center">
  <strong>Enterprise Financial Portfolio Management Dashboard</strong><br>
  <em>Track, manage, and grow your wealth with precision.</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/version-3.2.1-brightgreen" alt="Version">
  <img src="https://img.shields.io/badge/license-MIT-blue" alt="License">
  <img src="https://img.shields.io/badge/PHP-7.4+-purple" alt="PHP">
  <img src="https://img.shields.io/badge/status-production-success" alt="Status">
</p>

---

> [!WARNING]
> **This is a deliberately vulnerable web application designed for penetration testing education and CTF practice.**
> 
> This application contains intentional security flaws and should **NEVER** be deployed in production, exposed to the internet, or used with real data. Only run in isolated lab environments, sandbox VMs, or CTF platforms. The authors assume no liability for misuse.

---

## About NexusLedger

NexusLedger is a comprehensive financial portfolio management platform designed for both individual investors and corporate finance teams. Born from the need to simplify multi-account financial tracking, NexusLedger provides a unified dashboard where users can monitor balances, execute transfers, generate detailed reports, and manage financial documents. All from one elegant interface.

Originally developed as an internal tool for a mid-sized investment firm managing over $50M in client assets, NexusLedger evolved into a standalone product used by portfolio managers, accountants, and financial advisors to streamline their daily operations.

### Key Features

- **Unified Portfolio Dashboard** - View all your accounts, balances, and recent activity at a glance
- **Seamless Fund Transfers** - Transfer funds between accounts with multi-step verification
- **Transaction Tracking** - Search, filter, and audit every transaction with detailed logs
- **Document Management** - Upload and organize invoices, receipts, and financial statements
- **Advanced Reporting** - Generate portfolio summaries, monthly statements, quarterly reviews, and annual reports
- **REST API** - Integrate with third-party tools and automate your financial workflows
- **Role-Based Access** - Admin, manager, and user roles with granular permissions
- **Audit Trail** - Complete system activity logging for compliance and review
- **Multi-Layer Security** - Configurable security levels with session management and API key authentication

---

## Quick Start

NexusLedger runs on a standard LAMP stack and can be deployed in minutes.

```bash
# One-click install (Ubuntu/Debian/Kali)
sudo bash install.sh

# After VM reboot, start all services
sudo bash start.sh

# Clean removal
sudo bash uninstall.sh
```

Then open `http://<server-ip>/NexusLedger` in your browser and run the setup wizard.

### Requirements

| Component | Minimum Version |
|-----------|----------------|
| Apache | 2.4+ |
| MariaDB / MySQL | 10.3+ / 5.7+ |
| PHP | 7.4+ |

PHP Extensions: `mysqli`, `pdo_mysql`, `gd`, `mbstring`, `curl`

---

## Manual Installation

1. Clone or copy all files to your web root (`/var/www/html/NexusLedger`)
2. Import the database schema: `mysql -u root < database/schema.sql`
3. Update `config/config.php` with your database credentials
4. Visit `/NexusLedger/setup.php` to complete the setup
5. Login and start managing your portfolio

---

## Default Access

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Administrator |
| `john.doe` | `password123` | Manager |
| `jane.smith` | `password123` | User |
| `bob.wilson` | `password123` | User |
| `alice.brown` | `password123` | Manager |

> Demo API Key: `demo`

---

## Project Structure

```
NexusLedger/
├── admin/                 # Administrative panel
│   ├── api.php            # API key management dashboard
│   ├── audit.php          # System audit trail viewer
│   └── users.php          # User account management
├── api/
│   └── index.php          # REST API endpoints
├── assets/
│   ├── css/main.css       # Application stylesheet
│   ├── images/            # Icons, favicon, and brand assets
│   └── js/app.js          # Client-side behaviors
├── config/
│   └── config.php         # Database and application configuration
├── database/
│   └── schema.sql         # Complete schema with sample data
├── includes/
│   ├── auth.php           # Authentication and session logic
│   ├── db.php             # Database connection layer
│   └── page.php           # Page template engine
├── reports/               # Report template partials
│   ├── annual.php
│   ├── default.php
│   ├── monthly.php
│   └── quarterly.php
├── index.php              # Login / authentication page
├── dashboard.php          # Main portfolio dashboard
├── transactions.php       # Transaction history and search
├── transfer.php           # Inter-account fund transfers
├── documents.php          # Document upload and management
├── reports.php            # Report generation and export
├── profile.php            # User profile and account settings
├── security.php           # Security configuration
├── setup.php              # First-run database setup wizard
├── logout.php             # Session termination
├── install.sh             # Automated deployment script
├── uninstall.sh           # Cleanup and removal script
└── start.sh               # Service initialization script
```

---

## Security

NexusLedger implements multiple security layers including session-based authentication, role-based access control, CSRF protection, CSP headers, and API key validation. The security level is configurable via the admin panel to match your organization's compliance requirements.

SOC 2 Type II certified infrastructure. All sensitive operations are logged to the audit trail.

---

## License

MIT License. See [LICENSE](LICENSE) file for details.

---

<p align="center">
  <sub>Built with ❤️ for financial teams everywhere | &copy; 2026 NexusLedger Financial Systems</sub>
</p>
