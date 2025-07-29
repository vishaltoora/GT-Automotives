#!/bin/bash

echo "=== Git Pull and Conflict Resolution ==="

# Navigate to project directory
cd /Users/vishaltoora/projects/gt-automotives-web-page

echo "Current directory: $(pwd)"
echo "Current branch: $(git branch --show-current)"

# Fetch latest changes
echo "Fetching latest changes..."
git fetch origin

# Check if there are any conflicts
echo "Checking for conflicts..."
git status --porcelain

# Pull changes
echo "Pulling changes from origin..."
git pull origin feature/mysql-migration

# Check status after pull
echo "Status after pull:"
git status

echo "=== Done ===" 