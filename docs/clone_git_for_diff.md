# clone_git_for_diff.php

A CLI tool for cloning Drupal modules/profiles and copying their .git folders for diffing, enabling easy comparison between installed versions and their original repositories.

## Overview

This script automates the process of:
- Reading composer.lock to identify Drupal modules and profiles
- Cloning repositories to a temporary location
- Checking out the exact installed version
- Copying .git folders to installed module/profile directories
- Enabling git diff operations in module directories

## Features
- **Composer Integration:** Automatically identifies modules from composer.lock
- **Interactive:** Confirms each step with clear prompts and options
- **Batch Processing:** Supports processing multiple modules in one run
- **Dry Run:** Preview actions without making changes
- **Auto-confirm:** Option to skip all prompts
- **Progress Feedback:** Clear status messages and error handling

## Usage

```sh
php scripts/clone_git_for_diff.php [options]
```

### Common options
- `--debug`: Enable debug output
- `--dry-run`: Show what would be done without actually doing it
- `--yes` or `-y`: Automatically answer 'yes' to all prompts

### Example workflow

1. **Preview changes without making them:**
   ```sh
   php scripts/clone_git_for_diff.php --dry-run
   ```
2. **Process all modules automatically:**
   ```sh
   php scripts/clone_git_for_diff.php --yes
   ```
3. **Interactive processing with debug info:**
   ```sh
   php scripts/clone_git_for_diff.php --debug
   ```

## Requirements
- PHP CLI
- Git
- Composer (for reading composer.lock)
- SSH access to repositories (if using SSH URLs)

## How it works
- Reads composer.lock to identify Drupal modules and profiles
- For each module/profile:
  1. Clones repository to temporary location
  2. Checks out the exact version from composer.lock
  3. Copies .git folder to installed module directory
- Interactive mode provides three confirmation stages:
  - Clone repository
  - Checkout reference
  - Copy .git folder
- Each stage shows the exact commands that will be executed

## Interactive Prompts

For each module/profile, you'll see prompts like:

```
➡️ Processing ITCare-Company/ai_advanced_blocks (drupal-module)

❓ Do you want to clone the repository for ITCare-Company/ai_advanced_blocks?
Command that will be executed: git clone "git@github.com:ITCare-Company/ai_advanced_blocks.git" "/path/to/repos/ai_advanced_blocks"

Options:
  y (yes)    - Proceed with this action
  n (no)     - Skip this action
  s (skip)   - Skip this action and continue with next
  c (cancel) - Cancel the entire process
  a (all)    - Proceed with this and all remaining actions

Enter your choice [y/n/s/c/a]: 
```

## After Running

Once the script completes successfully, you can:

1. Navigate to any module directory:
   ```bash
   cd docroot/modules/contrib/some_module
   ```

2. Run git commands to see changes:
   ```bash
   git diff  # See all changes
   git log   # See commit history
   git status # See current state
   ```

## Troubleshooting

1. **SSH Access Issues**
   - Ensure your SSH keys are properly set up
   - Check your SSH agent is running
   - Verify you have access to the repositories

2. **Permission Issues**
   - Ensure you have write permissions in the target directories
   - Check if the script has permission to create the repos directory

3. **Git Issues**
   - If a repository is already cloned, the script will skip cloning
   - If a .git folder already exists, you'll be prompted to overwrite it

## Best Practices

1. Always run with `--dry-run` first to see what will happen
2. Use `--yes` when you're confident about the changes
3. Keep the repos directory clean by removing it after you're done
4. Make sure you have enough disk space for cloning repositories

## Related Scripts
- `analyze_git_changes.php`: For reviewing and pushing changes to modules
- `git_access_check_script_v1.php`: For checking repository access

## Extending/Contributing
- The script is designed to be modular and extensible
- PRs and improvements welcome!

---

*This script is part of the YMCA Website Services Drupal 11 upgrade automation toolkit.* 