#!/bin/bash

# GT Automotives - Git Setup Script
# This script helps set up git credentials using environment variables

echo "🚗 GT Automotives - Git Setup"
echo "=============================="

# Check if .env file exists
if [ ! -f .env ]; then
    echo "❌ .env file not found!"
    echo "Please create a .env file with your GitHub credentials:"
    echo "GITHUB_USERNAME=vishaltoora"
    echo "GITHUB_TOKEN=your_personal_access_token_here"
    exit 1
fi

# Load environment variables
source .env

# Check if credentials are set
if [ -z "$GITHUB_USERNAME" ] || [ "$GITHUB_TOKEN" = "your_personal_access_token_here" ]; then
    echo "❌ Please update your .env file with your actual GitHub credentials:"
    echo "GITHUB_USERNAME=vishaltoora"
    echo "GITHUB_TOKEN=your_actual_token_here"
    exit 1
fi

echo "✅ Environment variables loaded"
echo "Username: $GITHUB_USERNAME"

# Set git credentials
echo "🔧 Setting up git credentials..."

# Clear any existing credentials
git credential-osxkeychain erase <<< "protocol=https
host=github.com"

# Set the remote URL with credentials
git remote set-url origin "https://$GITHUB_USERNAME:$GITHUB_TOKEN@github.com/$GITHUB_USERNAME/GT-Automotives.git"

echo "✅ Git credentials configured"
echo ""
echo "🎯 Now you can push your code with:"
echo "   git push origin main"
echo ""
echo "⚠️  Remember: Never commit your .env file to git!"
echo "   It's already added to .gitignore for security." 