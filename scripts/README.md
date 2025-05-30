# Drupal Module Git Diff Scripts

## clone_git_for_diff.php

This script helps you clone Drupal modules/profiles and copy their .git folders for diffing. It's particularly useful when you need to compare the installed version of a module with its original repository.

### Purpose

The script:
1. Reads your composer.lock file to identify Drupal modules and profiles
2. Clones their repositories to a temporary location
3. Checks out the exact version you have installed
4. Copies the .git folder to your installed module/profile directory
5. Allows you to run `git diff` in the module/profile folders to see changes

### Prerequisites

- PHP CLI
- Git
- Composer (for reading composer.lock)
- SSH access to repositories (if using SSH URLs)

### Usage

```bash
php scripts/clone_git_for_diff.php [options]
```

### Options

- `--debug`: Enable debug output
- `--dry-run`: Show what would be done without actually doing it
- `--yes` or `-y`: Automatically answer 'yes' to all prompts

### Interactive Prompts

For each module/profile, the script will ask for confirmation at three stages:

1. **Clone Repository**
   - Prompts before cloning the repository
   - Shows the exact git clone command that will be executed

2. **Checkout Reference**
   - Prompts before checking out the specific commit/tag
   - Shows the exact git checkout command that will be executed

3. **Copy .git Folder**
   - Prompts before copying the .git folder to your installed module
   - Shows the source and destination paths

For each prompt, you can choose:
- `y` (yes): Proceed with this action
- `n` (no): Skip this action
- `s` (skip): Skip this action and continue with next
- `c` (cancel): Cancel the entire process
- `a` (all): Proceed with this and all remaining actions

### Example Output

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

### After Running

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

### Troubleshooting

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

### Best Practices

1. Always run with `--dry-run` first to see what will happen
2. Use `--yes` when you're confident about the changes
3. Keep the repos directory clean by removing it after you're done
4. Make sure you have enough disk space for cloning repositories

### Contributing

Feel free to submit issues and enhancement requests! 