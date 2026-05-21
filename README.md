# Backend

Small Laravel app I built for the assignment. It's a multi-tenant thing where companies have users with roles, and users can invite each other in and make short URLs.

Stack: Laravel 13, PHP 8.3, MySQL, session auth (no Sanctum tokens, just cookies). Frontend is plain Blade — no React/Vue, the spec said barebones HTML was fine.

## What it does

- **Companies** — SuperAdmin can create / rename / delete them. Deleting a company wipes its users, invitations and short URLs (FK cascade).
- **Users + roles** — five roles: `SuperAdmin`, `Admin`, `Member`, `Sales`, `Manager`. SuperAdmin is global (no company); everyone else belongs to one company.
- **Users page** — SuperAdmin sees everyone, Admin scoped to their company. Filter by company / role / search by name or email / sort. SuperAdmin can reset any user's password from the user detail page.
- **Invitations** — Admin/SuperAdmin send a signup link with a token. The invitee clicks it, picks a password, and gets logged in. The invitation row is kept after accept (marked with `accepted_at` + `accepted_user_id`) so the token can't be reused.
- **Short URLs** — anyone in a company (except SuperAdmin) can shorten a URL. Visibility:
  - SuperAdmin → sees all
  - Admin → sees the company's
  - Member/Sales/Manager → only their own
- Public resolver at `/s/{code}` — no auth, just redirects.

## What you need

- PHP 8.3+
- Composer
- Node 18+ and npm
- MySQL 5.7+ / 8.x running locally

## Database setup

This project uses MySQL with the database name **`Sembark`**.

```sql
-- run this in mysql / phpmyadmin first
CREATE DATABASE Sembark CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then in your `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Sembark
DB_USERNAME=root
DB_PASSWORD=               # blank for local XAMPP/MAMP, otherwise your mysql password
```

## Setup steps

```bash
# 1. install php + node deps
composer install
npm install

# 2. copy env and gen app key
cp .env.example .env
php artisan key:generate

# 3. make sure your .env has the MySQL block above + email block below

# 4. run migrations + seed the SuperAdmin and demo companies
php artisan migrate --seed

# 5. build frontend assets (or use `npm run dev` for hot reload)
npm run build
```

## Running it

```bash
php artisan serve
```

Then open http://localhost:8000.

For full dev mode (server + queue + log tail + vite all at once):

```bash
composer run dev
```

## Default login

The seeder creates one SuperAdmin and 3 demo companies:

- email: `superadmin@example.com`
- password: `password`

Companies seeded: Acme Corp, Tech Solutions, Global Industries.

The SuperAdmin doesn't belong to any company — they're global. Use the SuperAdmin to invite Admins into the demo companies, then those Admins can invite Members/Sales/Managers.

## Email setup (Gmail SMTP)

The invitation flow sends a real email with the accept link. The easiest way to make that work in local dev is Gmail SMTP with an **app password** (not your normal Gmail password — Google blocks those).

**1. Generate a Gmail app password:**
- Go to https://myaccount.google.com/apppasswords (you must have 2-step verification enabled first)
- Pick "Mail" + your device name → Google gives you a 16-character password like `abcd efgh ijkl mnop`
- Copy it (you only see it once)

**2. Put these in your `.env`:**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD="paste-the-16-char-app-password-here"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="URL Shortener SaaS"
```

**3. Test it** by sending an invitation from `/invitations`. The invitee should get a real email with the accept link.

**Heads up:**
- Never commit a real app password to git. `.env` is gitignored — keep it that way.
- If you don't want to set up SMTP, set `MAIL_MAILER=log` instead. Emails get written to `storage/logs/laravel.log` — grep for the accept link and paste it manually.

## Migrations

Standard Laravel stuff:

```bash
php artisan migrate            # apply pending
php artisan migrate:fresh      # drop everything and re-run
php artisan migrate:fresh --seed   # drop + re-run + seed (most useful for dev)
php artisan migrate:rollback   # roll back the last batch
```

Tables created (in order):

1. `companies` — id, name, timestamps
2. `users` — has `role` and nullable `company_id` FK (null for SuperAdmin)
3. `invitations` — company_id, email, role, token, invited_by, accepted_at, accepted_user_id
4. `short_urls` — original_url, short_code (unique 8 chars), company_id, user_id

All FKs cascade on delete, so killing a company tears down everything below it.

## Project layout

```
app/
  Http/Controllers/
    AuthController.php         # login/logout, session-based
    CompanyController.php      # SuperAdmin-only CRUD
    InvitationController.php   # send + accept invites
    ShortUrlController.php     # CRUD + /s/{code} resolver
    UserController.php         # users list + detail + password reset
  Models/                      # Company, User, Invitation, ShortUrl
  Policies/                    # InvitationPolicy, ShortUrlPolicy
  Mail/InvitationMail.php      # the invite email itself

database/
  migrations/                  # all migrations
  seeders/DatabaseSeeder.php   # SuperAdmin + 3 companies, uses raw SQL per spec

resources/views/               # Blade templates (auth, dashboard, companies, users, etc.)
routes/web.php                 # all routes
```

Authorization for invitations and short-urls lives in Policies. Company auth is just a simple guard method inside `CompanyController` since only SuperAdmin touches it — a full Policy felt like overkill there.

## Tests

```bash
php artisan test
# or
composer run test
```

There's a feature test for the short URL + invitation flow in `tests/Feature/ShortUrlTest.php`.

## Debug helpers in controllers

Each controller method has a commented `// var_dump(...); // die();` block. Uncomment when you need to inspect what's flowing through that method, then re-comment when done. Don't push them uncommented or the page will white-screen.
