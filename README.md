# Backend

Small Laravel app I built for the assignment. It's a multi-tenant thing where companies have users with roles, and users can invite each other in and make short URLs.

Stack: Laravel 13, PHP 8.3, SQLite (default), session auth (no Sanctum tokens, just cookies). Frontend is plain Blade — no React/Vue, the spec said barebones HTML was fine.

## What it does

- **Companies** — SuperAdmin can create / rename / delete them. Deleting a company wipes its users, invitations and short URLs (FK cascade).
- **Users + roles** — five roles: `SuperAdmin`, `Admin`, `Member`, `Sales`, `Manager`. SuperAdmin is global (no company); everyone else belongs to one company.
- **Invitations** — Admin/SuperAdmin email a signup link with a token. The invitee clicks it, picks a password, and gets logged in. Invitation row is deleted after they accept.
- **Short URLs** — anyone in a company (except SuperAdmin) can shorten a URL. Visibility:
  - SuperAdmin → sees all
  - Admin → sees the company's
  - Member/Sales/Manager → only their own
- Public resolver at `/s/{code}` — no auth, just redirects.

## Setup

You need PHP 8.3+, Composer, Node 18+ and npm. SQLite is built in so no DB server to install.

```bash
# 1. install php + node deps
composer install
npm install

# 2. copy env and gen app key
cp .env.example .env
php artisan key:generate

# 3. make the sqlite file (if it doesn't exist)
touch database/database.sqlite

# 4. run migrations + seed the SuperAdmin and demo companies
php artisan migrate --seed

# 5. build frontend assets (or use `npm run dev` for hot reload)
npm run build
```

There's also a composer shortcut that does most of this:

```bash
composer run setup
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
3. `invitations` — company_id, email, role, token, invited_by
4. `short_urls` — original_url, short_code (unique 8 chars), company_id, user_id

All FKs cascade on delete, so killing a company tears down everything below it.

## Email

Mailer is set to `log` in `.env.example`, so invitation emails get written to `storage/logs/laravel.log` instead of actually being sent. Grep the log for the accept link, or swap `MAIL_MAILER` to `smtp` and fill in real SMTP creds if you want real emails.

## Project layout

```
app/
  Http/Controllers/
    AuthController.php         # login/logout, session-based
    CompanyController.php      # SuperAdmin-only CRUD
    InvitationController.php   # send + accept invites
    ShortUrlController.php     # CRUD + /s/{code} resolver
  Models/                       # Company, User, Invitation, ShortUrl
  Policies/                     # InvitationPolicy, ShortUrlPolicy
  Mail/InvitationMail.php       # the invite email itself

database/
  migrations/                   # 4 migrations (see above)
  seeders/DatabaseSeeder.php    # SuperAdmin + 3 companies, uses raw SQL per spec

resources/views/                # Blade templates (auth, dashboard, companies, etc.)
routes/web.php                  # all routes
```

Authorization for invitations and short-urls lives in Policies. Company auth is just a simple guard method inside `CompanyController` since only SuperAdmin touches it — a full Policy felt like overkill there.

## Tests

```bash
php artisan test
# or
composer run test
```

There's a feature test for the short URL flow in `tests/Feature/ShortUrlTest.php`.

## Debug helpers in controllers

Each controller method has a commented `// var_dump(...); // die();` block right before its return. Uncomment when you need to inspect what's flowing through that method, then re-comment when done. Don't push them uncommented or the page will white-screen.
