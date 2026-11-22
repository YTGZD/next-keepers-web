# Next Keepers: marketing site + dashboard preview (Laravel)

Public site for nextkeepers.com: an acquisition intelligence platform for business buyers (secondary: owners, investors). The product is an MVP in development; this site promotes early access and includes a buyer dashboard preview.

## Stack
Laravel 13, PHP 8.3, Blade, Tailwind CSS v4 (tokens in `resources/css/app.css`), Alpine.js, Vite, SQLite by default (MySQL-ready via `.env`). Fonts are self-hosted from npm (Inter, Fraunces). No external CDNs.

## Commands
- `composer setup` equivalent: `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate`
- Dev: `npm run dev` and `php artisan serve`
- Seed the dashboard sample data: `php artisan migrate --seed` (or `php artisan db:seed --class=NextKeepersPlatformSeeder`)
- Build: `npm run build` (required before the app or its tests can render pages)
- Test: `php artisan test`
- Export signups: `php artisan signups:export`
- Create or promote an admin (prompts for a password): `php artisan nk:admin you@example.com` (`--revoke` to undo). Admins can only be made this way; `users.role` is not mass-assignable.
- Deploy: merging to `main` deploys. The server (Docker Compose behind Traefik, `/home/ubuntu/sites/nextkeepers`, `src/` is a checkout of this repo) runs `auto-deploy.sh` from cron every 2 minutes (copy in `deploy/auto-deploy.sh`): DB backup, rebuild, restart; the container entrypoint runs migrate + `db:seed` + optimize on every start, so keep migrations and seeders idempotent and safe unattended. Filament's public assets are committed (the Docker build doesn't run composer scripts). The admin footer shows the live `BUILD`. Admin on the server: `docker exec -it nextkeepers php artisan nk:admin you@example.com`.
- Import listings (big files, on the server): `php artisan nk:import-listings path/to/file.xlsx --preset=western-ny|healthcare|all [--county=Erie] [--dry-run]`. Smaller files can be uploaded in the admin panel (Listings > Import listings). Both go through `app/Services/Listings/ListingImporter.php`: upsert by Listing Key, new rows are drafts, review fields (status, verification, admin notes) are never overwritten, sample rows are never touched. The full 104 MB workbook is slow to read with PHP; import filtered exports of it.
- Regenerate the hero map data: `node scripts/generate-us-map.mjs` (writes `resources/data/us-map.json`)

## Structure
- `resources/views/home/*.blade.php`: one partial per homepage section; `home.blade.php` stitches them together.
- `resources/views/components/`: `x-button`, `x-logo` (official logo SVGs in `public/images/logo/`), `x-eyebrow`, `x-sample-badge`, `x-us-map`, `x-legal-page`, `x-layouts.marketing`.
- `resources/js/app.js`: Alpine components (`usMap`, `earlyAccess`) and scroll reveal.
- `app/Http/Controllers/AuthController.php`: email + password login/register/logout. `/login` and `/register` redirect home with `?auth=login|register`, which opens the site-wide Alpine modal (`x-auth-modal`). POSTs are `guest`-guarded and throttled 6/min.
- `app/Http/Controllers/DashboardController.php` + `resources/views/dashboard/*`: the buyer dashboard (dashboard, discover, business detail, buy box, market intelligence, watchlist, messages, settings). Layout: `x-layouts.dashboard`. Matching logic: `app/Services/BuyBoxMatchingService.php`.
- `app/Models/Business.php`: one row per listing. Fictional preview rows have `is_sample = true`; real rows carry `listing_key` (unique, e.g. `bizbuysell:2535967`) and the fields of the cleaned listings workbook ("Clean Listings" sheet). New rows start as `status = draft`, `is_verified = false`; only `Business::visible()` statuses (active, under_loi, sold) are ever shown to buyers. Missing figures are `null` ("not disclosed"), never 0.
- `app/Support/ListingMetrics.php`: multiples and ratios (`$business->metrics()`), mirroring the workbook's formula columns. Don't store derived ratios on the row.
- `app/Filament/`: the admin panel at `/admin` (Filament 5). Only `role = admin` users can sign in (`User::canAccessPanel`); Filament rate-limits the login and there is no sign-up or reset there. Resources: Listings (review queue, publish/archive, sample rows read-only and hidden by default), Users (role changes, never your own), Early-access signups, Inquiries, Newsletter. Filament closures must name the query parameter `$query` (Filament injects by name; any other name gets an empty builder). Avatars are inline SVG initials (`InitialsAvatarProvider`), not ui-avatars.com. Filament's public assets live in `public/{js,css,fonts}/filament` and are committed; re-run `php artisan filament:assets` after upgrading Filament.
- `app/Http/Controllers/EarlyAccessController.php`: signup endpoint (JSON or normal POST, honeypot, throttle 5/min, upsert by email).

## Dashboard and auth model
- Guests see a **read-only preview** of the fictional sample rows only. Signed-in accounts see only real, published listings (`App\Services\Listings\ListingFeed`; pages in `resources/views/dashboard/real/`, controller `RealDashboardController`), matched on their buy box by `BuyBoxMatcher` (industry, states, counties, price, revenue, earnings, profitable). Undisclosed figures never count against a listing, and the fit score is the share of criteria known to be met. Sample and real rows never mix. New accounts start with an empty buy box. Market intelligence, messages and settings still carry sample content for everyone.
- The guest preview is backed by one seeded sample account (`NK_DEMO_EMAIL`, default `demo@nextkeepers.invalid`, random unusable password; nobody can log in as it). Without the seed the dashboard returns 503 with the seed command.
- Registered users see their own profile, buy box, watchlist, pipeline, messages and notifications. Every write route (POST/DELETE) is behind the `auth` middleware; a 401 from `fetch` opens the auth modal (`app.js`).
- New accounts start as "Free account", "No NDA on file", no verification, no 2FA. Never add default "Verified"/NDA/SBA/2FA claims to migrations, models or seeders.
- Listings, market figures and the demo user's messages are fictional seed data. The dashboard shows an "Illustrative sample data" banner; keep it. Do not bake stats into images (header art is `public/images/dashboard/mountain-clean.jpg`).
- Not built yet: password reset, email verification, 2FA, profile photo upload, billing/"Upgrade Plan", Pipeline and Reports pages. Don't present these as working.

## Product-integrity rules (from the design blueprint)
- Every mock figure, listing or map point must be labelled illustrative (`x-sample-badge` or a caption). Never present estimates as verified facts.
- No fake testimonials; use clearly attributed mission/founder statements until real, permissioned quotes exist.
- Do not claim nationwide coverage, verified off-market inventory, automated outreach or valuation certainty.
- Respect `prefers-reduced-motion`; keep all interactive controls keyboard accessible with visible focus.

## Palette
Forest `#10291F`, emerald `#1B604A`, gold `#C99B43`, ivory `#F5F2EA`, navy `#17283D`, sage `#718477`.
