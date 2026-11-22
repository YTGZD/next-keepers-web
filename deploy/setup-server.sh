#!/usr/bin/env bash
# One-time setup of Next Keepers on an Ubuntu server (22.04 or 24.04), e.g. an
# Oracle Cloud free-tier VM. Safe to run again: every step checks first.
#
# Usage (as the normal login user, e.g. "ubuntu", which has sudo):
#   curl -fsSL https://raw.githubusercontent.com/YTGZD/next-keepers-web/main/deploy/setup-server.sh -o setup-server.sh
#   APP_DOMAIN=app.nextkeepers.com bash setup-server.sh
#
# What it does:
#   - installs PHP 8.3 (+ extensions), Composer, Node 22, git, nginx (if no web server yet)
#   - clones the app to /var/www/next-keepers-web, creates .env and the SQLite database
#   - adds an nginx site for $APP_DOMAIN on ports 80 and 443 (self-signed cert for
#     Cloudflare "Full" mode; Cloudflare shows visitors its own valid certificate)
#   - installs a systemd timer that checks GitHub every 2 minutes and deploys new
#     commits on main (deploy/deploy.sh), so merged changes go live by themselves
# It never touches other nginx sites (your current design page keeps working).
set -euo pipefail
umask 0002

APP_DOMAIN="${APP_DOMAIN:-app.nextkeepers.com}"
APP_DIR="${APP_DIR:-/var/www/next-keepers-web}"
REPO_URL="${REPO_URL:-https://github.com/YTGZD/next-keepers-web.git}"
DEPLOY_USER="${DEPLOY_USER:-$(id -un)}"
WEB_GROUP="www-data"
PHP_VER="8.3"

say() { printf '\n\033[1;32m==> %s\033[0m\n' "$*"; }
die() { printf '\n\033[1;31mERROR: %s\033[0m\n' "$*" >&2; exit 1; }

[ "$(id -u)" -ne 0 ] || die "Run this as your normal user (e.g. ubuntu), not root. It uses sudo where needed."
. /etc/os-release
[ "${ID:-}" = "ubuntu" ] || die "This script is for Ubuntu (found ${ID:-unknown})."

# Another web server already owning ports 80/443 (e.g. the current design page)?
if ! command -v nginx >/dev/null 2>&1; then
  for other in apache2 caddy httpd; do
    if systemctl is-active --quiet "$other" 2>/dev/null; then
      die "$other is running and serves ports 80/443. This script adds an nginx site, so it stops here instead of breaking your current site. Ask Claude to add a $other config for $APP_DOMAIN instead."
    fi
  done
fi

say "Installing system packages (PHP $PHP_VER, nginx, git, sqlite)"
sudo apt-get update -qq
if ! apt-cache show "php${PHP_VER}-fpm" >/dev/null 2>&1; then
  sudo apt-get install -y -qq software-properties-common
  sudo add-apt-repository -y ppa:ondrej/php
  sudo apt-get update -qq
fi
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
  git unzip curl ca-certificates openssl sqlite3 nginx \
  "php${PHP_VER}-fpm" "php${PHP_VER}-cli" "php${PHP_VER}-sqlite3" "php${PHP_VER}-mbstring" \
  "php${PHP_VER}-xml" "php${PHP_VER}-curl" "php${PHP_VER}-zip" "php${PHP_VER}-intl" \
  "php${PHP_VER}-bcmath" "php${PHP_VER}-gd"

if ! command -v composer >/dev/null 2>&1; then
  say "Installing Composer"
  curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
  sudo php /tmp/composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
  rm -f /tmp/composer-setup.php
fi

if ! command -v node >/dev/null 2>&1 || [ "$(node -p 'process.versions.node.split(".")[0]')" -lt 20 ]; then
  say "Installing Node 22"
  curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash - >/dev/null
  sudo apt-get install -y -qq nodejs
fi

# Small VMs (1 GB RAM) run out of memory during composer/npm builds without swap.
if [ "$(free -m | awk '/^Mem:/{print $2}')" -lt 2000 ] && ! swapon --show | grep -q .; then
  say "Adding 2 GB swap (small server)"
  sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile && sudo mkswap /swapfile >/dev/null && sudo swapon /swapfile
  grep -q '^/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab >/dev/null
fi

say "Getting the app into $APP_DIR"
sudo mkdir -p "$APP_DIR"
sudo chown "$DEPLOY_USER:$WEB_GROUP" "$APP_DIR"
if [ ! -d "$APP_DIR/.git" ]; then
  git clone --quiet "$REPO_URL" "$APP_DIR"
fi
cd "$APP_DIR"

if [ ! -f .env ]; then
  cp .env.example .env
  sed -i \
    -e 's/^APP_ENV=.*/APP_ENV=production/' \
    -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' \
    -e "s#^APP_URL=.*#APP_URL=https://${APP_DOMAIN}#" \
    -e 's/^LOG_LEVEL=.*/LOG_LEVEL=warning/' \
    .env
fi
mkdir -p database && touch database/database.sqlite

say "Setting file permissions (app runs as $WEB_GROUP through PHP-FPM)"
sudo chown -R "$DEPLOY_USER:$WEB_GROUP" "$APP_DIR"
sudo chmod -R g+rwX storage bootstrap/cache database
sudo find storage bootstrap/cache database -type d -exec chmod g+s {} +

say "First build and deploy"
NK_FORCE=1 bash deploy/deploy.sh
grep -q '^APP_KEY=.\+' .env || php artisan key:generate --force
php artisan config:cache >/dev/null

say "Configuring nginx for $APP_DOMAIN"
CERT_DIR=/etc/ssl/next-keepers
if [ ! -f "$CERT_DIR/origin.crt" ]; then
  sudo mkdir -p "$CERT_DIR"
  sudo openssl req -x509 -nodes -newkey rsa:2048 -days 3650 \
    -keyout "$CERT_DIR/origin.key" -out "$CERT_DIR/origin.crt" -subj "/CN=${APP_DOMAIN}" 2>/dev/null
  sudo chmod 600 "$CERT_DIR/origin.key"
fi
sed -e "s#__APP_DOMAIN__#${APP_DOMAIN}#g" -e "s#__APP_DIR__#${APP_DIR}#g" -e "s#__CERT_DIR__#${CERT_DIR}#g" deploy/nginx-site.conf | sudo tee /etc/nginx/sites-available/next-keepers >/dev/null
sudo ln -sf /etc/nginx/sites-available/next-keepers /etc/nginx/sites-enabled/next-keepers
sed -e "s#__DEPLOY_USER__#${DEPLOY_USER}#g" deploy/php-fpm-pool.conf | sudo tee "/etc/php/${PHP_VER}/fpm/pool.d/next-keepers.conf" >/dev/null
sudo systemctl restart "php${PHP_VER}-fpm"
sudo nginx -t
sudo systemctl enable --now "php${PHP_VER}-fpm" nginx >/dev/null 2>&1
sudo systemctl reload nginx

say "Opening ports 80 and 443 in the server firewall (Oracle images block them by default)"
for port in 80 443; do
  if ! sudo iptables -C INPUT -p tcp --dport "$port" -m state --state NEW -j ACCEPT 2>/dev/null; then
    sudo iptables -I INPUT 1 -p tcp --dport "$port" -m state --state NEW -j ACCEPT
  fi
done
if command -v netfilter-persistent >/dev/null 2>&1; then sudo netfilter-persistent save >/dev/null; fi

say "Installing auto-deploy (checks GitHub every 2 minutes)"
sed -e "s#__APP_DIR__#${APP_DIR}#g" -e "s#__DEPLOY_USER__#${DEPLOY_USER}#g" deploy/nk-deploy.service \
  | sudo tee /etc/systemd/system/nk-deploy.service >/dev/null
sudo cp deploy/nk-deploy.timer /etc/systemd/system/nk-deploy.timer
sudo systemctl daemon-reload
sudo systemctl enable --now nk-deploy.timer >/dev/null

say "Done"
cat <<MSG

  App folder:   $APP_DIR
  Site:         https://${APP_DOMAIN}  (after the Cloudflare DNS record below)
  Version live: $(cat "$APP_DIR/BUILD" 2>/dev/null || echo unknown)

  Next steps:
  1. Cloudflare > nextkeepers.com > DNS > Add record:
       Type A, Name "app", IPv4 = this server's public IP, Proxy ON (orange cloud).
  2. Cloudflare > SSL/TLS: mode "Full" works with this server (so does "Flexible").
  3. In Oracle Cloud, the subnet's security list must allow TCP 80 and 443 from 0.0.0.0/0
     (it already does if your current site loads).
  4. Create your admin login:
       cd $APP_DIR && php artisan nk:admin you@example.com
  5. Watch deploys:   journalctl -u nk-deploy -f
MSG
