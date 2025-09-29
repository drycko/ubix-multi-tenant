#!/bin/bash

# Directory containing tenant models
MODELS_DIR="app/Models/Tenant"

# Backup the models first
echo "Creating backup of tenant models..."
timestamp=$(date +%Y%m%d_%H%M%S)
backup_dir="storage/backup_models_${timestamp}"
mkdir -p "${backup_dir}"
cp -r "${MODELS_DIR}"/* "${backup_dir}/"
echo "Backup created in ${backup_dir}"

# Function to fix a model file
fix_model() {
    file="$1"
    echo "Fixing $file..."
    
    # Create a temporary file
    temp_file="${file}.tmp"
    
    # Fix namespace and use statements
    sed -i.bak '
        # Fix namespace
        s/namespace App\\\\App\\\\Models\\\\Tenant\\\\Tenant\\\\Tenant;/namespace App\\Models\\Tenant;/g
        s/namespace App\\\\App\\\\Models\\\\Tenant\\\\Tenant;/namespace App\\Models\\Tenant;/g
        s/namespace App\\\\App\\\\Models\\\\Tenant;/namespace App\\Models\\Tenant;/g
        s/namespace App\\\\Models;/namespace App\\Models\\Tenant;/g
        
        # Fix PropertyScope import
        s|use App\\\\App\\\\Models\\\\Tenant\\\\Tenant\\\\Scopes\\\\PropertyScope|use App\\Models\\Scopes\\PropertyScope|g
        s|use App\\\\Models\\\\Tenant\\\\Scopes\\\\PropertyScope|use App\\Models\\Scopes\\PropertyScope|g
        
        # Fix duplicate SoftDeletes
        s/use HasFactory, SoftDeletes;, SoftDeletes/use HasFactory, SoftDeletes;/g
    ' "$file"
    
    # Remove backup file
    rm "${file}.bak"
    
    echo "Fixed $file"
}

# Process each model file
for file in ${MODELS_DIR}/*.php; do
    if [ -f "$file" ]; then
        fix_model "$file"
    fi
done

echo "All models have been fixed. Please review the changes."
echo "Backup is available in ${backup_dir}"