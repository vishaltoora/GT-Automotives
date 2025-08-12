#!/bin/bash

echo "=== Resolving Specific Git Conflicts ==="

cd /Users/vishaltoora/projects/gt-automotives-web-page

echo "Current directory: $(pwd)"
echo "Current branch: $(git branch --show-current)"

echo "Resolving conflicts in database files..."

# Resolve each conflicted file - keep our MySQL changes
echo "Resolving database/init_db.php..."
git checkout --ours database/init_db.php
git add database/init_db.php

echo "Resolving database/schema.sql..."
git checkout --ours database/schema.sql
git add database/schema.sql

echo "Resolving includes/db_connect.php..."
git checkout --ours includes/db_connect.php
git add includes/db_connect.php

echo "Committing resolved conflicts..."
git commit -m "Resolve merge conflicts - keeping MySQL migration changes in database files"

echo "Pushing resolved conflicts..."
git push origin feature/mysql-migration

echo "Final status:"
git status --porcelain

echo "=== Conflict Resolution Complete ===" 