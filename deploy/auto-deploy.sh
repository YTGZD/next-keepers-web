#!/bin/sh
# Auto-deploy nextkeepers.com when main moves on GitHub.
#
# Lives on the server at /home/ubuntu/sites/nextkeepers/auto-deploy.sh (this is
# the versioned copy) and runs from the ubuntu user's crontab every 2 minutes:
#   */2 * * * * /home/ubuntu/sites/nextkeepers/auto-deploy.sh >/dev/null 2>&1
#
# Server layout: Docker Compose behind Traefik (Let's Encrypt).
#   sites/nextkeepers/src        git checkout of this repo (main)
#   sites/nextkeepers/deploy.sh  backs up the SQLite DB, resets src to origin/main,
#                                docker compose build + up -d, waits for HTTP 200
#   sites/nextkeepers/data/      database + storage volumes (persist across builds)
# The container entrypoint runs migrate, db:seed and optimize on every start.
#
# Writes src/BUILD ("<commit> <time>") before building; the admin panel footer
# shows it, so you can see which version is live. Force a rebuild: NK_FORCE=1.
cd /home/ubuntu/sites/nextkeepers || exit 1
exec 9>/tmp/nk-auto-deploy.lock
flock -n 9 || exit 0
git -C src fetch -q origin main || exit 1
if [ "$(git -C src rev-parse HEAD)" = "$(git -C src rev-parse origin/main)" ] && [ -f src/BUILD ] && [ "${NK_FORCE:-}" != "1" ]; then exit 0; fi
printf '%s %s\n' "$(git -C src rev-parse --short origin/main)" "$(date -u '+%Y-%m-%d %H:%M UTC')" > src/BUILD
echo "=== $(date -u) deploying $(git -C src log -1 --format='%h %s' origin/main)" >> auto-deploy.log
./deploy.sh >> auto-deploy.log 2>&1
echo "=== $(date -u) exit $?" >> auto-deploy.log
