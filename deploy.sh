#!/usr/bin/env bash
# =============================================================================
# deploy.sh — DigitalOcean one-command deploy script
#
# Usage on Droplet:
#   chmod +x deploy.sh && ./deploy.sh [branch]
# =============================================================================

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BRANCH="${1:-main}"

# -----------------------------------------------------------------------------
# 1. Install Docker if missing
# -----------------------------------------------------------------------------
if ! command -v docker &>/dev/null; then
    echo "==> Docker not found. Installing..."
    curl -fsSL https://get.docker.com | sh
    systemctl enable --now docker
    echo "     Docker installed."
else
    echo "==> Docker already installed ($(docker --version))."
fi

# Install Docker Compose plugin if missing (v2 — 'docker compose')
if ! docker compose version &>/dev/null 2>&1; then
    echo "==> Docker Compose plugin not found. Installing..."
    apt-get update -qq
    apt-get install -y -qq docker-compose-plugin
    echo "     Docker Compose plugin installed."
else
    echo "==> Docker Compose already installed ($(docker compose version --short))."
fi

# -----------------------------------------------------------------------------
# 2. Pull latest code
# -----------------------------------------------------------------------------
echo "==> [2] Pulling latest code from branch '$BRANCH'..."
cd "$APP_DIR"
git fetch --all
git reset --hard "origin/$BRANCH"

# -----------------------------------------------------------------------------
# 3. Copy .env
# -----------------------------------------------------------------------------
echo "==> [3] Checking .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    # Generate a 64-char JWT secret automatically
    JWT=$(openssl rand -hex 32)
    sed -i "s|JWT_SECRET=.*|JWT_SECRET=$JWT|" .env
    echo "     .env created with a fresh JWT_SECRET."
    echo "     Please review .env and set DB_PASS / DB_ROOT_PASS before continuing."
    echo "     Then re-run: ./deploy.sh"
    exit 0
fi

# -----------------------------------------------------------------------------
# 4. Build & start containers
# -----------------------------------------------------------------------------
echo "==> [4] Building and starting containers..."
docker compose down --remove-orphans
docker compose up -d --build

# -----------------------------------------------------------------------------
# 5. Wait for MySQL
# -----------------------------------------------------------------------------
echo "==> [5] Waiting for MySQL to be ready..."
RETRIES=30
until docker compose exec -T db mysqladmin ping -h localhost --silent 2>/dev/null || [ $RETRIES -eq 0 ]; do
    printf '.'
    sleep 3
    RETRIES=$((RETRIES - 1))
done
echo " ready."

if [ $RETRIES -eq 0 ]; then
    echo "ERROR: MySQL did not become ready in time." >&2
    exit 1
fi

# -----------------------------------------------------------------------------
# 6. Install Composer dependencies
# -----------------------------------------------------------------------------
echo "==> [6] Installing Composer dependencies (production)..."
docker compose exec -T app composer install --no-dev --optimize-autoloader --no-interaction

# -----------------------------------------------------------------------------
# 7. Summary
# -----------------------------------------------------------------------------
echo ""
echo "✓ Deployment complete."
echo "  App:  http://vending.thinzarnwe.com"
echo "  After SSL: https://vending.thinzarnwe.com"
echo ""
echo "  Container status:"
docker compose ps
