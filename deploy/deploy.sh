#!/usr/bin/env bash
# Deploys the latest commit on main, if there is one. Run by the nk-deploy
# systemd timer every 2 minutes; safe to run by hand any time:
#   bash /var/www/next-keepers-web/deploy/deploy.sh          (only if main changed)
#   NK_FORCE=1 bash /var/www/next-keepers-web/deploy/deploy.sh   (rebuild anyway)
set -euo pipefail
umask 0002
cd "$(dirname "$0")/.."

BRANCH="${NK_BRANCH:-main}"
exec 9>"storage/framework/deploy.lock"
flock -n 9 || { echo "Another deploy is running."; exit 0; }

git fetch --quiet origin "$BRANCH"
LOCAL="$(git rev-parse HEAD)"
REMOTE="$(git rev-parse "origin/$BRANCH")"
if [ "$LOCAL" = "$REMOTE" ] && [ "${NK_FORCE:-}" != "1" ] && [ -f BUILD ]; then
  exit 0
fi

echo "Deploying $(git log -1 --format='%h %s' "origin/$BRANCH")"
php artisan down --retry=15 >/dev/null 2>&1 || true
trap 'php artisan up >/dev/null 2>&1 || true' EXIT

git reset --quiet --hard "origin/$BRANCH"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --quiet
npm ci --no-audit --no-fund --silent
npm run build --silent >/dev/null
grep -q '^APP_KEY=.\+' .env || php artisan key:generate --force
php artisan migrate --force --no-interaction
# The public dashboard preview needs the sample account; the seeder only upserts.
php artisan db:seed --class=NextKeepersPlatformSeeder --force --no-interaction >/dev/null
php artisan optimize >/dev/null
php artisan filament:optimize >/dev/null 2>&1 || true

printf '%s %s\n' "$(git rev-parse --short HEAD)" "$(date -u '+%Y-%m-%d %H:%M UTC')" > BUILD
echo "Live: $(cat BUILD)"
