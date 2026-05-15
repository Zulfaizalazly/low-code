# Ar-Rahnumation — Low-Code Platform

A low-code / no-code feature management platform for **Ar-Rahnu (Islamic Pawnbroking)** operations. HQ administrators design workflows, forms, rules, and approvals visually; branch staff execute them in a runtime portal — no traditional release cycle required.

Built on Laravel 13, Livewire 4, and Vue 3 (Vue Flow).

---

## What's inside

The platform is split into three layers:

| Layer        | Path             | Responsibility                                                        |
| ------------ | ---------------- | --------------------------------------------------------------------- |
| **Studio**   | `app/Studio`     | Authoring tools — flow builder, page builder, rules, AI, publishing   |
| **Runtime**  | `app/Runtime`    | Executes published features — automation engine, dynamic UI, sims     |
| **Domain**   | `app/Domain`     | Ar-Rahnu business modules — customer, facility, valuation, payment, vault, document, notification, approval, accounting, compliance |

Studio features include a Flow Builder (Vue Flow), Page Builder / Form Engine, Rule Engine, Formula Engine, Blueprint Registry, Publish & Release Management, Scope Overrides, AI-assisted generation, and a Simulation harness. See `BRS_ArRahnumation.md` for the full business spec.

---

## Tech stack

- **PHP** 8.3+ / **Laravel** 13
- **Livewire** 4 (server-driven UI)
- **Vue** 3 + **Vue Flow** (visual builders)
- **Tailwind CSS** 4 + **Vite** 8
- **Spatie Laravel Permission** (RBAC)
- **SQLite** by default (any Laravel-supported DB works)

---

## Getting started

### Prerequisites

- PHP 8.3+ with the usual Laravel extensions
- Composer 2.x
- Node.js 20+ and npm

### Install

```bash
git clone <repo-url> low-code
cd low-code
composer setup
```

`composer setup` runs `composer install`, copies `.env.example` → `.env`, generates the app key, runs migrations, installs npm packages, and builds frontend assets.

### Configure

Edit `.env` as needed. Notable keys:

```env
APP_URL=http://localhost
DEMO_MODE=true                # enables demo login shortcuts
DB_CONNECTION=sqlite          # swap to mysql/pgsql if preferred

# AI-assisted feature generation (optional)
OPENAI_API_KEY=
AI_MODEL=gpt-5.2
AI_MONTHLY_BUDGET=50.00
```

### Seed demo data

```bash
php artisan migrate --seed
```

This creates demo accounts:

| Role           | Email                  |
| -------------- | ---------------------- |
| HQ Admin       | `admin@arrahnu.com`    |
| Branch Manager | `manager1@arrahnu.com` |
| Teller / Staff | `staff1@arrahnu.com`   |

### Run

```bash
composer dev
```

Boots `php artisan serve`, the queue worker, log tailer (`pail`), and Vite together via `concurrently`. App is served at `http://localhost:8000`.

To run them individually:

```bash
php artisan serve
php artisan queue:listen --tries=1 --timeout=0
npm run dev
```

---

## Useful routes

| Route                              | Purpose                          |
| ---------------------------------- | -------------------------------- |
| `/`                                | Landing page with SSO modal      |
| `/studio`                          | Studio authoring environment     |
| `/admin`                           | HQ admin panel                   |
| `/branch`                          | Branch manager dashboard         |
| `/portal/operations/new-pledge`    | Teller portal — pledge intake    |

When `DEMO_MODE=true`, `/login-hq`, `/login-manager`, `/login-teller`, etc. are available as one-click logins (deprecated — prefer the SSO modal).

---

## Testing & code style

```bash
composer test                 # php artisan test (PHPUnit 12)
./vendor/bin/pint             # Laravel Pint — code formatter
```

---

## Project layout

```
app/
├── Studio/        # Discovery, Registry, Publishing, Scoping, Validation, AI
├── Runtime/       # Automation, UI, Simulation, Models
├── Domain/        # Customer, Facility, Valuation, Payment, ...
├── Livewire/      # Livewire components
├── Http/          # Controllers, middleware, requests
├── Models/        # Eloquent models
└── Services/      # Cross-cutting services
resources/
├── views/         # Blade + Livewire views
├── js/            # Vue components (builders, runtime UI)
└── css/
routes/
├── web.php        # Web + Studio API routes
└── api.php
database/
├── migrations/
├── seeders/
└── factories/
```

---

## Deployment

A reference deploy script is provided at `deploy.sh`, and a PM2 ecosystem file at `ecosystem.config.cjs`. Adjust both to match your hosting target before using in production.

---

## Documentation

- **`BRS_ArRahnumation.md`** — full Business Requirements Specification covering Studio, Runtime, Domain features, data model, NFRs, and the Ar-Rahnu blueprint catalogue.

---

## License

MIT.
