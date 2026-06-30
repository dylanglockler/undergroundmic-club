# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This App Is

Guest management system for **The Underground Mic** — a monthly basement speakeasy karaoke party (last Saturday of each month, 7PM America/Chicago). Guests sign up on the public homepage, choose a reminder method (email, calendar, or SMS), and receive automated reminders before the event. Hosts manage guests and send blasts via a Filament admin panel at `/admin`.

## Commands

```bash
# First-time setup
composer setup

# Full dev environment (Laravel server + queue worker + Pail log viewer + Vite, all concurrent)
composer dev

# Run tests (clears config first)
composer test

# Run a single test
php artisan test --filter=TestClassName

# Code formatting/linting
./vendor/bin/pint

# Build frontend assets
npm run build
```

## Architecture

**Stack:** Laravel 13 / PHP 8.3, Filament 5, Tailwind CSS 4, Vite 8, MySQL (SQLite in tests)

**Key env vars required:** `ANTHROPIC_API_KEY`, `POSTMARK_TOKEN`, `ADMIN_PASSWORD`

### Core Flows

**Public signup** (`routes/web.php` → `GuestController`):
- Guest submits form on homepage, choosing reminder method (email/calendar/text) and timing (1week/1day/dayof)
- On success, calls Claude (`claude-sonnet-4-6`) to generate a personalized 3-4 sentence confirmation message

**Automated reminders** (`app/Console/Commands/SendPartyReminders.php`):
- Runs daily at 10AM via scheduler (defined in `routes/console.php`)
- Sends email reminders to eligible guests based on `reminder_time` field; tracks delivery via `reminded_at`

**Admin panel** (`/admin`, Filament):
- `GuestResource` — full CRUD on the guests table with sorting/filtering
- `Blast` page — AI-assisted message drafting; calls Claude to generate email subjects/bodies or SMS copy, then sends via Postmark or opens native SMS

### Models

**`Guest`** — core domain model: `name`, `stage_name` (optional), `phone` (optional), `method` (email|calendar|text), `contact` (email or phone), `reminder_time` (1week|1day|dayof), `reminded_at`

**`User`** — Filament admin users; must have the `host` role (Spatie permission) to access `/admin`. Seeded via `AdminSeeder`.

### Permissions & Auth

Spatie `laravel-permission` manages roles. Only the `host` role grants admin panel access (`User::canAccessPanel()`). Initial host users are created by `AdminSeeder` (Cliff, Molly, Dylan).

### Party Date Calculation

The next party date (last Saturday of each month at 7PM America/Chicago) is computed with Carbon and is **duplicated** in `web.php`, `GuestController`, `SendPartyReminders`, and `Blast`. Any change to party timing must be updated in all four places.

### AI Integration

Claude API calls are made directly via HTTP in `GuestController@draftConfirmation` and `Blast@callClaude`. The API key comes from `config/services.php` → `ANTHROPIC_API_KEY`. Model: `claude-sonnet-4-6`, API version header: `2023-06-01`.

### Email

Postmark is the mail driver. The `PartyReminderMail` mailable renders `resources/views/emails/party-reminder.blade.php` (Markdown). From address: `noreply@undergroundmic.club`.

### Queue & Sessions

All drivers (queue, session, cache) use the `database` driver. Run `php artisan queue:work` (or use `composer dev` which starts it automatically).
