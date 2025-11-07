#!/usr/bin/env bash
set -euo pipefail

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
ZIP_NAME="deploy_${TIMESTAMP}.zip"

echo "Building assets..."
composer install --no-dev --optimize-autoloader
npm ci
npm run build

echo "Creating zip package: $ZIP_NAME"
zip -r "$ZIP_NAME" . \
  -x ".git/*" \
     "node_modules/*" \
     "storage/debug/*" \
     ".env" \
     "deployment.zip" \
     "docker-compose*" \
     "Dockerfile"

echo "Done: $ZIP_NAME"
       
echo "✅ Package created: $ZIP_NAME"
echo "📦 Size: $(du -h "$ZIP_NAME" | cut -f1)"
