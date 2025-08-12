# Manual Git Conflict Resolution Guide

## 🚨 **You have conflicts in your git pull request**

Here's how to resolve them step by step:

## 📋 **Step 1: Check Current Status**

```bash
cd /Users/vishaltoora/projects/gt-automotives-web-page
git status
```

## 📋 **Step 2: See Which Files Have Conflicts**

```bash
git status --porcelain | grep "^UU"
```

## 📋 **Step 3: Resolve Conflicts**

### **Option A: Keep Your Changes (Recommended)**

Since you have the MySQL migration fixes, you probably want to keep your changes:

```bash
# For each conflicted file, keep your version
git checkout --ours <filename>
git add <filename>
```

### **Option B: Use Remote Changes**

```bash
# For each conflicted file, use remote version
git checkout --theirs <filename>
git add <filename>
```

### **Option C: Manual Resolution**

1. Open each conflicted file in your editor
2. Look for conflict markers: `<<<<<<<`, `=======`, `>>>>>>>`
3. Choose which version to keep
4. Remove the conflict markers
5. Save the file
6. `git add <filename>`

## 📋 **Step 4: Commit the Resolution**

```bash
git commit -m "Resolve merge conflicts - keeping MySQL migration fixes"
```

## 📋 **Step 5: Push the Resolution**

```bash
git push origin feature/mysql-migration
```

## 🔧 **Quick Resolution Script**

If you want to automatically keep your changes for PHP files:

```bash
# Run this in your terminal
cd /Users/vishaltoora/projects/gt-automotives-web-page

# Get conflicted files
conflicted_files=$(git diff --name-only --diff-filter=U)

# Resolve each conflict
for file in $conflicted_files; do
    if [[ $file == *.php ]]; then
        echo "Keeping our changes for: $file"
        git checkout --ours "$file"
        git add "$file"
    else
        echo "Using remote version for: $file"
        git checkout --theirs "$file"
        git add "$file"
    fi
done

# Commit the resolution
git commit -m "Resolve merge conflicts - keeping MySQL migration fixes"
git push origin feature/mysql-migration
```

## 🎯 **What This Does**

- **PHP files**: Keeps your MySQL migration fixes
- **Other files**: Uses the remote version
- **Commits**: The resolved conflicts
- **Pushes**: The resolution to GitHub

## 📞 **Need Help?**

If you're still having issues, you can:

1. Share the specific conflict messages
2. I can help you resolve them one by one
3. Or we can create a fresh branch with your changes
