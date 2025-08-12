#!/bin/bash

cd /Users/vishaltoora/projects/gt-automotives-web-page

echo "Current branch:"
git branch --show-current

echo "Fetching changes..."
git fetch origin

echo "Current status:"
git status --porcelain

echo "Pulling changes..."
git pull origin feature/mysql-migration

echo "Final status:"
git status --porcelain 