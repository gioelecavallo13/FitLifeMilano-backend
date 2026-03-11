#!/bin/bash
# Script di cache per il deploy in produzione.
# Eseguire dopo ogni deploy: ./deploy-cache.sh

set -e
cd "$(dirname "$0")"

echo "Esecuzione: php artisan config:cache"
php artisan config:cache

echo "Esecuzione: php artisan route:cache"
php artisan route:cache

echo "Esecuzione: php artisan view:cache"
php artisan view:cache

echo ""
echo "Cache artefact completata con successo."
