# analyze_git_changes.php

A CLI tool for reviewing, staging, and pushing changes to contributed and custom Drupal modules/profiles, with access-aware automation and robust git workflow support.

## Overview

This script automates the process of:
- Detecting local changes in all modules/profiles with a `.git` directory
- Checking if you have write access to each repository (GitHub or Drupal.org)
- Caching access status for speed (with force/clear options)
- Interactive review of changes (git status, git diff)
- Selectively staging files, creating branches, committing, and pushing
- Always using the correct SSH URL for pushes (avoids HTTP Basic errors)
- Allowing continuation if files were staged in a previous run

## Features
- **Access-aware:** Only presents writable packages for review
- **Caching:** Fast access checks with `repo_access_cache.json` (can force/clear)
- **Interactive:** Add files, skip, next, quit, or branch/push at any step
- **Branch & Push:** Create a branch, commit, and push staged changes with one command
- **SSH URL Handling:** Always uses canonical SSH URLs for GitHub and Drupal.org
- **Restartable:** If you staged files in a previous run, you can continue where you left off

## Usage

```sh
php scripts/analyze_git_changes.php [options]
```

### Common options
- `--interactive` or `-i` : Enable interactive review (required for staging/pushing)
- `--debug` : Show debug output
- `--force-access-check` : Ignore cache, check access for all repos live
- `--clear-access-cache` : Delete the access cache before running
- `--access-check <repo-url>` : Check access for a specific repo and exit

### Example workflow

1. **Review and stage changes interactively:**
   ```sh
   php scripts/analyze_git_changes.php --interactive
   ```
2. **Force re-check of all repo access:**
   ```sh
   php scripts/analyze_git_changes.php --interactive --force-access-check
   ```
3. **Clear the access cache:**
   ```sh
   php scripts/analyze_git_changes.php --clear-access-cache
   ```
4. **Check access for a specific repo:**
   ```sh
   php scripts/analyze_git_changes.php --access-check https://git.drupalcode.org/project/your_module.git
   ```

## Requirements
- PHP 8.1+
- Composer dependencies:
  - `guzzlehttp/guzzle`
  - `vlucas/phpdotenv`
- `.env` file with:
  - `GITHUB_TOKEN` (GitHub PAT)
  - `GITLAB_DO_TOKEN` (Drupal.org GitLab PAT)

## How it works
- Reads all modules/profiles in `docroot/modules/contrib` and `docroot/profiles/contrib`
- Checks access using cached results in `repo_access_cache.json` (project root)
- Interactive mode lets you:
  - See unstaged and staged changes
  - Add files to staging
  - Create a branch, commit, and push (always uses SSH URL)
  - Continue, skip, or quit at any step
- If you staged files in a previous run, the script will show them and let you continue or push

## Troubleshooting
- **Push fails with HTTP Basic error:** The script now always uses SSH URLs for pushes. If you see a wrong URL, check your remote and the `cleanRepoUrl` logic.
- **Access check fails:** Ensure your tokens are valid and not expired. Use `--force-access-check` if needed.
- **Cache issues:** Use `--clear-access-cache` to reset the cache.
- **.env not loaded:** Place your `.env` in the `scripts/` or project root directory.

## Cache file
- Access status is cached in `repo_access_cache.json` in the project root.
- This file is safe to delete; it will be rebuilt as needed.

## Extending/Contributing
- See `scripts/git_access_util.php` for utility functions
- Related script: `scripts/git_access_check_script_v1.php` (bulk access check)
- PRs and improvements welcome!

---

*This script is part of the YMCA Website Services Drupal 11 upgrade automation toolkit.* 