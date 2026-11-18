#!/usr/bin/env bash
# ==============================================================================
# Bridge-Tailor AI Business Diagnostic — Hostinger Shared Hosting SSH Deployer
# ==============================================================================
# Architecture:
#   - Private App Core:  ~/domains/<your-domain>/bridge        (OUTSIDE public_html)
#   - Public Web Root:   ~/domains/<your-domain>/public_html   (or subdomain public_html)
# ==============================================================================

set -e

DOMAIN_DIR="$(pwd)"
APP_DIR="${DOMAIN_DIR}/bridge"
PUBLIC_DIR="${DOMAIN_DIR}/public_html"
REPO_URL="https://github.com/eabventures-champion/bridge-tailor.git"

echo "==> 1. Cloning or updating Bridge-Tailor core in: ${APP_DIR}"
if [ -d "${APP_DIR}/.git" ]; then
    cd "${APP_DIR}"
    git pull origin main
else
    git clone "${REPO_URL}" "${APP_DIR}"
    cd "${APP_DIR}"
fi

echo "==> 2. Installing production Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> 3. Preparing .env configuration..."
if [ ! -f "${APP_DIR}/.env" ]; then
    cp "${APP_DIR}/.env.example" "${APP_DIR}/.env"
    php artisan key:generate --force
    echo "    [!] Created ${APP_DIR}/.env — remember to set your Hostinger MySQL DB_* credentials and run: php artisan migrate --seed --force"
fi

echo "==> 4. Linking public_html to bridge/public (keeping core outside public_html)..."
mkdir -p "${PUBLIC_DIR}"
cp -f "${APP_DIR}/public/.htaccess" "${PUBLIC_DIR}/.htaccess"
cp -f "${APP_DIR}/public/favicon.ico" "${PUBLIC_DIR}/favicon.ico" 2>/dev/null || true
cp -f "${APP_DIR}/public/robots.txt" "${PUBLIC_DIR}/robots.txt" 2>/dev/null || true

cat > "${PUBLIC_DIR}/index.php" << 'EOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Point directly to the private ../bridge folder outside public_html
$bridgeBase = __DIR__ . '/../bridge';

if (file_exists($maintenance = $bridgeBase . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $bridgeBase . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once $bridgeBase . '/bootstrap/app.php';

// Ensure Laravel knows public_html is the public path
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
EOF

echo "==> 5. Setting storage and cache permissions..."
chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

echo "==> Deployment complete!"
echo "    Private Core : ${APP_DIR}"
echo "    Public Web   : ${PUBLIC_DIR}"
