# AI Invoicing System

A comprehensive AI-powered invoicing and billing management system built with **Laravel 12**, **Inertia.js v2**, **Vue 3**, and **Tailwind CSS v4**.

> ⚠️ **This is proprietary software.** See [LICENSE](./LICENSE) for details. No copying, modification, or distribution is permitted.

---

## Tech Stack

| Layer      | Technology                        |
| ---------- | --------------------------------- |
| Backend    | PHP 8.4, Laravel 12               |
| Frontend   | Vue 3, Inertia.js v2              |
| Styling    | Tailwind CSS v4                   |
| Database   | SQLite (default)                  |
| PDF        | DomPDF (barryvdh/laravel-dompdf)  |
| Rich Text  | Tiptap Editor                     |
| Icons      | Lucide Vue Next                   |
| Routing    | Laravel Wayfinder                 |
| Testing    | Pest v3                           |

---

## Prerequisites

- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 18.x
- **npm** >= 9.x

---

## Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/manan-monani/Ai-Invoicing-System.git
cd Ai-Invoicing-System
```

### 2. Quick Setup (Recommended)

Run the built-in setup script that handles everything:

```bash
composer setup
```

This will:
- Install PHP dependencies (`composer install`)
- Copy `.env.example` to `.env` (if not exists)
- Generate application key
- Run database migrations
- Install Node.js dependencies (`npm install`)
- Build frontend assets (`npm run build`)

### 3. Manual Setup (Alternative)

If you prefer to set up manually:

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Install Node.js dependencies
npm install

# Build frontend assets
npm run build
```

---

## Running the Application

### Development Mode (Recommended)

Start all services concurrently (server, queue, logs, and Vite HMR):

```bash
composer run dev
```

This launches:
- 🌐 **Laravel Server** — `http://127.0.0.1:8000`
- ⚡ **Vite Dev Server** — Hot module replacement
- 📋 **Queue Worker** — Background job processing
- 📝 **Pail Logs** — Real-time log viewer

### Individual Services

```bash
# PHP development server only
php artisan serve

# Vite frontend dev server only
npm run dev

# Build production assets
npm run build
```

---

## Application Structure

### Admin Portal (`/admin`)
- **Dashboard** — Revenue metrics, invoice counts, top customers
- **Invoices** — Create, edit, preview, download PDF, email
- **Catalog** — Items, categories, tax management
- **Members** — Employee management with role-based access
- **Customers** — Directory with invoice history and balances
- **Roles & Permissions** — Granular access control matrix
- **Settings** — Branding, business logic, tax config, payment methods
- **Activity Logs** — Full audit trail

### Customer Portal (`/customer`)
- **Dashboard** — Paid/outstanding balances
- **Invoices** — View and pay invoices
- **Profile** — Account management

### Public Pages
- **Landing Page** — Marketing page with features, pricing, FAQ
- **Contact Us** — Contact form
- **Auth** — Login and registration

---

## Code Quality

```bash
# Run tests
php artisan test --compact

# Lint PHP code
vendor/bin/pint --dirty

# Lint frontend code
npm run lint

# Format frontend code
npm run format
```

---

## Environment Variables

All configuration is managed via `.env`. Key variables:

| Variable             | Description              | Default       |
| -------------------- | ------------------------ | ------------- |
| `APP_NAME`           | Application name         | `Laravel`     |
| `APP_URL`            | Application URL          | `localhost`   |
| `DB_CONNECTION`      | Database driver          | `sqlite`      |
| `QUEUE_CONNECTION`   | Queue driver             | `database`    |
| `MAIL_MAILER`        | Mail driver              | `log`         |

See [.env.example](./.env.example) for all available options.

---

## License

**All Rights Reserved.** This software is proprietary. See [LICENSE](./LICENSE) for full terms.

No copying, modification, distribution, or contribution is permitted without explicit written consent.
