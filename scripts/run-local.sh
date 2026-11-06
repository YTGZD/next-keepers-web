#!/usr/bin/env bash
# One-command local setup and run for the Next Keepers site.
# Usage:  ./scripts/run-local.sh        (installs what is needed, then serves http://127.0.0.1:8000)
# Set NK_NO_SERVE=1 to set everything up without starting the server.
set -euo pipefail
cd "$(dirname "$0")/.."

need() {
  command -v "$1" >/dev/null 2>&1 || { echo "Missing '$1'. $2"; exit 1; }
}
need php      'Install PHP 8.3+ and Composer. On a Mac, run:  /bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.4)"  then open a NEW Terminal window and rerun this script.'
need composer 'It is installed together with PHP by the php.new command above.'
need node     'Install Node 20+ from https://nodejs.org (LTS), then open a NEW Terminal window and rerun this script.'
need npm      'It comes with Node.'

php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
  || { echo "PHP 8.3 or newer is required (found $(php -r 'echo PHP_VERSION;'))."; exit 1; }

[ -f .env ] || cp .env.example .env
composer install --no-interaction --prefer-dist
grep -q '^APP_KEY=.\+' .env || php artisan key:generate --ansi
mkdir -p database && [ -f database/database.sqlite ] || touch database/database.sqlite
php artisan migrate --force --no-interaction
npm install --no-audit --no-fund
npm run build

if [ "${NK_NO_SERVE:-}" = "1" ]; then echo "Setup complete. Start the site with: php artisan serve"; exit 0; fi

echo ""
echo "Next Keepers is running at http://127.0.0.1:8000  (press Ctrl+C to stop)"
( sleep 3; open http://127.0.0.1:8000 2>/dev/null || xdg-open http://127.0.0.1:8000 2>/dev/null || true ) &
php artisan serve --host=127.0.0.1 --port=8000
