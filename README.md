# 🚀 PitoHub — cPanel Hosting Management & REST API

**PitoHub** is a modern, high-performance cPanel & WHM hosting management platform and RESTful API built on **Laravel 12 / PHP 8.4**. It enables hosting providers, web agencies, SaaS platforms, and developers to manage multi-server cPanel clusters, provision accounts, control email addresses, databases, subdomains, cron jobs, and perform single-click Single Sign-On (SSO) logins into cPanel accounts.

---

## 🌟 Key Features

- **Multi-Server cPanel / WHM Integration**:
  - Connect unlimited WHM nodes (via WHM API Token or root password).
  - Test server connectivity & monitor load averages in real time.
  - Bidirectional sync: Pull remote cPanel accounts directly into PitoHub.
  - **Simulation Mode**: Built-in mock mode allows local development, testing, and UI preview without requiring an active WHM server.
- **cPanel Account Lifecycle**:
  - Provision new cPanel hosting accounts with configurable disk, bandwidth, package, and domain settings.
  - Instant Suspend / Unsuspend with reason tracking and timestamping.
  - 1-Click cPanel SSO Magic Link (`/cpsess...` session generation).
  - Terminate accounts cleanly from both the WHM server and database.
  - Change passwords and upgrade/downgrade hosting plans.
- **Granular Resource Controls**:
  - **Email Accounts**: Add POP/IMAP accounts with disk quotas, manage forwarders, and delete mailboxes.
  - **MySQL Databases & Users**: Create databases, create DB users, assign privileges (`ALL PRIVILEGES`).
  - **Domains & Subdomains**: Add addon domains, subdomains, and aliases with document root configurations.
  - **Cron Jobs**: Schedule background jobs with custom cron expressions.
- **Developer-First REST API (Sanctum Authenticated)**:
  - 40+ RESTful API endpoints under `/api/v1/...`.
  - Token-based API authentication (`Bearer <TOKEN>`).
  - Standard JSON response format `{ success: true, data: ... }`.
- **Interactive Web Management Dashboard**:
  - Sleek, modern dark-mode responsive dashboard.
  - Live API Explorer & Documentation directly inside the app.
  - Full audit trail & activity logs.

---

## 🛠️ Quick Start

### 1. Requirements
- PHP 8.2 or higher (PHP 8.4 supported)
- Composer 2.x
- SQLite (default) or MySQL / MariaDB

### 2. Installation
```bash
# Navigate to the project directory
cd PitoHub

# Install dependencies (already completed)
composer install

# Set up environment file
cp .env.example .env
php artisan key:generate

# Run database migrations and seed realistic demo data
php artisan migrate:fresh --seed
```

### 3. Create Admin & Generate API Token
```bash
php artisan pitohub:create-admin --email=admin@pitohub.local --password=password123
```
This command outputs your Sanctum API Bearer Token immediately.

### 4. Start the Application
```bash
php artisan serve
```
Then visit **`http://localhost:8000`** in your browser to access the management dashboard and interactive API explorer.

---

## 📡 REST API Reference (`/api/v1`)

### Headers Required for Protected Endpoints:
```http
Authorization: Bearer <YOUR_SANCTUM_TOKEN>
Accept: application/json
Content-Type: application/json
```

### 1. Authentication
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/v1/auth/login` | Log in and receive a Sanctum Bearer token |
| `POST` | `/api/v1/auth/register` | Register a new user |
| `GET` | `/api/v1/auth/me` | Fetch authenticated user profile |
| `POST` | `/api/v1/auth/logout` | Revoke current access token |

#### Example Login Request:
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "admin@pitohub.local", "password": "password123"}'
```

---

### 2. WHM Servers Management
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/servers` | List all connected WHM servers |
| `POST` | `/api/v1/servers` | Connect a new WHM server |
| `GET` | `/api/v1/servers/{id}` | Get server details and account count |
| `PUT` | `/api/v1/servers/{id}` | Update server connection details |
| `DELETE`| `/api/v1/servers/{id}` | Remove server from PitoHub |
| `POST` | `/api/v1/servers/{id}/test-connection` | Test connection to WHM API |
| `POST` | `/api/v1/servers/{id}/sync-accounts` | Pull all cPanel accounts from WHM into database |
| `GET` | `/api/v1/servers/{id}/stats` | Get server load averages & hardware stats |

---

### 3. Hosting Accounts (cPanel)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/accounts` | List accounts (filter by `server_id`, `search`, `is_suspended`) |
| `POST` | `/api/v1/accounts` | Provision account on cPanel/WHM & store in database |
| `GET` | `/api/v1/accounts/{id}` | Get account details with emails, DBs, and domains |
| `PUT` | `/api/v1/accounts/{id}` | Update account plan, limits, and notes |
| `DELETE`| `/api/v1/accounts/{id}` | Terminate account from cPanel and database |
| `POST` | `/api/v1/accounts/{id}/suspend` | Suspend cPanel account with a reason |
| `POST` | `/api/v1/accounts/{id}/unsuspend` | Unsuspend cPanel account |
| `POST` | `/api/v1/accounts/{id}/change-password`| Change account cPanel password |
| `POST` | `/api/v1/accounts/{id}/sso-url` | Generate temporary 1-Click cPanel magic login session |

#### Example Account Creation:
```bash
curl -X POST http://localhost:8000/api/v1/accounts \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "server_id": 1,
    "domain": "store.example.com",
    "username": "examplestore",
    "password": "SecurePassword123!",
    "contact_email": "admin@example.com",
    "plan": "Standard",
    "disk_limit_mb": 10240
  }'
```

---

### 4. Sub-Resources
- **Email Accounts**:
  - `GET /api/v1/accounts/{id}/emails`
  - `POST /api/v1/accounts/{id}/emails` (payload: `{"email": "info@domain.com", "password": "...", "quota_mb": 1024}`)
  - `DELETE /api/v1/accounts/{id}/emails/{emailId}`
- **MySQL Databases**:
  - `GET /api/v1/accounts/{id}/databases`
  - `POST /api/v1/accounts/{id}/databases` (payload: `{"database_name": "app", "create_user": true, "db_username": "appuser", "db_password": "..."}`)
  - `DELETE /api/v1/accounts/{id}/databases/{databaseId}`
- **Domains & Subdomains**:
  - `GET /api/v1/accounts/{id}/domains`
  - `POST /api/v1/accounts/{id}/domains` (payload: `{"domain_name": "sub.domain.com", "type": "subdomain", "document_root": "public_html/sub"}`)
  - `DELETE /api/v1/accounts/{id}/domains/{domainId}`
- **Cron Jobs**:
  - `GET /api/v1/accounts/{id}/cronjobs`
  - `POST /api/v1/accounts/{id}/cronjobs` (payload: `{"schedule": "0 * * * *", "command": "/usr/bin/php ...", "description": "Hourly sync"}`)
  - `DELETE /api/v1/accounts/{id}/cronjobs/{cronjobId}`

---

### 5. System Stats & Audit Logs
- `GET /api/v1/stats`: Returns aggregated metrics (servers, accounts, storage percentage, bandwidth).
- `GET /api/v1/activity-logs`: Paginated audit logs with IP, action name, user, and timestamps.

---

## 💻 Artisan Console Commands

```bash
# Sync accounts from all active WHM servers (or specific server by ID)
php artisan pitohub:sync-server {server_id?}

# Create a new administrator account and retrieve an API token
php artisan pitohub:create-admin --email=user@domain.com --password=secret
```

---

## 🧪 Testing

PitoHub comes with a comprehensive feature test suite covering authentication, account provisioning, suspension toggling, cPanel SSO session generation, sub-resource lifecycle, and metrics:

```bash
php artisan test
```

All 10 feature tests pass cleanly with 33 assertions.

---

## 🔒 Security Notes
- WHM API tokens and passwords stored in the database are encrypted using Laravel's native Eloquent `encrypted` casting.
- All destructive actions (account termination, database deletion) are logged with IP addresses in the `activity_logs` table.
- Sanctum personal access tokens allow secure, revocable API access per client or integration.
