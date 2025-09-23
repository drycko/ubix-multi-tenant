#!/bin/bash

# Deployment script for Laravel to cPanel
echo "🚀 Starting Laravel deployment..."

# Create timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
ZIP_NAME="deploy_${TIMESTAMP}.zip"

# Create a COMPLETE zip including vendor and proper structure
zip -r "$ZIP_NAME" . \
    -x ".git/*" \
       "node_modules/*" \
       ".env" \
       "deployment.zip" \
       "docker-compose*" \
       "Dockerfile"
       
echo "✅ Package created: $ZIP_NAME"
echo "📦 Size: $(du -h "$ZIP_NAME" | cut -f1)"
