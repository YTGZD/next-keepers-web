# Next Keepers

Marketing website for [nextkeepers.com](https://nextkeepers.com): homepage, early-access signup, privacy and terms pages. Built with Laravel 13, Blade, Tailwind CSS 4 and Alpine.js.

## Run it locally

Requires PHP 8.3+, Composer and Node 20+. The quickest way, from the project folder:

```bash
./scripts/run-local.sh      # sets everything up, builds, opens http://127.0.0.1:8000
```

On a Mac without PHP yet: `/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.4)"`, install Node LTS from nodejs.org, then open a new Terminal window and run the script. Or do the same steps by hand:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # skip if it already exists
php artisan migrate
npm run build                    # or `npm run dev` while developing
php artisan serve                # http://127.0.0.1:8000
```

## Tests

```bash
php artisan test
```

## Early-access signups

Signups are stored in the `early_access_signups` table (email, optional name, interest, consent timestamp, hashed IP). Export them any time:

```bash
php artisan signups:export                       # storage/app/early-access-signups.csv
php artisan signups:export storage/app/list.csv  # custom path
```

## Settings (`.env`)

| Variable | Purpose |
|---|---|
| `APP_URL` | Public URL, e.g. `https://nextkeepers.com` |
| `NK_CONTACT_EMAIL` | Shown in the footer and legal pages when set |
| `NK_LINKEDIN_URL`, `NK_YOUTUBE_URL`, `NK_X_URL` | Footer social icons appear only for URLs that are set |
| `DB_*` | Defaults to SQLite; switch to MySQL for production if you prefer |

## Deploying

The site needs a PHP 8.3 host, because Laravel can't run on GitHub Pages. On the server:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build            # or upload public/build from a machine with Node
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Point the web root at `public/`, set `APP_ENV=production`, `APP_DEBUG=false` and a real `APP_KEY`, and use HTTPS.

## Placeholders to replace

- **Logo:** `resources/views/components/logo.blade.php` is a temporary NK lockup; swap in the official SVG. Also replace `public/favicon.svg`.
- **Key animation:** the golden key section uses the supplied still image with a slide-in and slow float. Swap in the key-and-lock animation when it is ready.
- **Legal pages:** `resources/views/pages/privacy.blade.php` and `terms.blade.php` are plain-language drafts. Have a lawyer review them before launch.
- **Homepage data:** the map points, the sample opportunity card and the product preview are illustrative and labelled as such. Replace them with real data only once it is sourced and validated.

## Map data

`scripts/generate-us-map.mjs` builds `resources/data/us-map.json` (state outlines from US Atlas plus sample points). Rerun it with `node scripts/generate-us-map.mjs` after editing the city list.
