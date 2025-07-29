#!/bin/bash

echo "=== Git Conflict Resolution Script ==="

# Navigate to project directory
cd /Users/vishaltoora/projects/gt-automotives-web-page

echo "Current directory: $(pwd)"

# Check current branch
echo "Current branch:"
git branch --show-current

# Fetch latest changes
echo "Fetching latest changes..."
git fetch origin

# Check current status
echo "Current status:"
git status --porcelain

# Try to pull and see if there are conflicts
echo "Attempting to pull changes..."
if git pull origin feature/mysql-migration 2>&1 | grep -q "CONFLICT"; then
    echo "CONFLICTS DETECTED!"
    echo "Files with conflicts:"
    git status --porcelain | grep "^UU"
    
    echo "Resolving conflicts..."
    
    # List all conflicted files
    conflicted_files=$(git diff --name-only --diff-filter=U)
    
    for file in $conflicted_files; do
        echo "Resolving conflict in: $file"
        
        # For PHP files, we'll keep our changes (the MySQL fixes)
        if [[ $file == *.php ]]; then
            echo "Keeping our changes for PHP file: $file"
            git checkout --ours "$file"
            git add "$file"
        else
            echo "Using remote version for: $file"
            git checkout --theirs "$file"
            git add "$file"
        fi
    done
    
    # Commit the resolved conflicts
    echo "Committing resolved conflicts..."
    git commit -m "Resolve merge conflicts - keeping MySQL migration fixes"
    
else
    echo "No conflicts detected, pull successful!"
fi

echo "Final status:"
git status --porcelain

echo "=== Conflict Resolution Complete ===" 