# Next Keepers: marketing site (Laravel)

Public site for nextkeepers.com: an acquisition intelligence platform for business buyers (secondary: owners, investors). The product is an MVP in development; this site promotes early access.

## Stack
Laravel 13, PHP 8.3, Blade, Tailwind CSS v4 (tokens in `resources/css/app.css`), Alpine.js, Vite, SQLite by default (MySQL-ready via `.env`). Fonts are self-hosted from npm (Inter, Fraunces). No external CDNs.

## Commands
- `composer setup` equivalent: `composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate`
- Dev: `npm run dev` and `php artisan serve`
- Build: `npm run build` (required before the app or its tests can render pages)
- Test: `php artisan test`
- Export signups: `php artisan signups:export`
- Regenerate the hero map data: `node scripts/generate-us-map.mjs` (writes `resources/data/us-map.json`)

## Structure
- `resources/views/home/*.blade.php`: one partial per homepage section; `home.blade.php` stitches them together.
- `resources/views/components/`: `x-button`, `x-logo` (official logo SVGs in `public/images/logo/`), `x-eyebrow`, `x-sample-badge`, `x-us-map`, `x-legal-page`, `x-layouts.marketing`.
- `resources/js/app.js`: Alpine components (`usMap`, `earlyAccess`) and scroll reveal.
- `app/Http/Controllers/EarlyAccessController.php`: signup endpoint (JSON or normal POST, honeypot, throttle 5/min, upsert by email).

## Product-integrity rules (from the design blueprint)
- Every mock figure, listing or map point must be labelled illustrative (`x-sample-badge` or a caption). Never present estimates as verified facts.
- No fake testimonials; use clearly attributed mission/founder statements until real, permissioned quotes exist.
- Do not claim nationwide coverage, verified off-market inventory, automated outreach or valuation certainty.
- Respect `prefers-reduced-motion`; keep all interactive controls keyboard accessible with visible focus.

## Palette
Forest `#10291F`, emerald `#1B604A`, gold `#C99B43`, ivory `#F5F2EA`, navy `#17283D`, sage `#718477`.
