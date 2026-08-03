#!/bin/bash
set -e

PHP="/opt/cpanel/ea-php83/root/usr/bin/php"

echo "🚀 Deployment started..."

$PHP artisan down
echo "✅ Maintenance mode ON"

# Pull the latest code from Git
git pull origin dev
echo "✅ Git pull done"

# Cache clear
$PHP artisan optimize:clear
$PHP artisan filament:optimize-clear
echo "✅ Cache cleared"

# Composer install
$PHP composer.phar install --no-dev --optimize-autoloader
echo "✅ Composer done"

# Frontend assets build (npm)
if command -v npm >/dev/null 2>&1; then
    npm install
    npm run build
    echo "✅ Frontend assets built"
else
    echo "⚠️  npm not found on this server — public/build was NOT rebuilt."
    echo "⚠️  Build assets locally (npm run build) and upload the public/build folder manually."
fi

# Copy the .env file if it doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
    $PHP artisan key:generate
    echo "✅ .env created"
fi

# Migrate
$PHP artisan migrate --force
echo "✅ Migration done"

# Cache rebuild
$PHP artisan optimize
$PHP artisan filament:optimize
echo "✅ Cache rebuilt"

# Force the queue worker to load the new code
$PHP artisan queue:restart
echo "✅ Queue restarted"

$PHP artisan up
echo "✅ Maintenance mode OFF"

echo "🎉 Deployment finished!"
