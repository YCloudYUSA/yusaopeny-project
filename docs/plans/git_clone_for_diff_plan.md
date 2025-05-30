# Plan: Script to Enable Git Diff for Drupal Packages from Composer

## Status Legend
- ✅ **Done**
- 🟡 **In Progress**
- ⬜️ **To Do**
- ⏸️ **Postponed**
- ⛔️ **Blocked**

---

## Goal
Create a script to automate the process of cloning all Drupal packages (modules and profiles) listed in `composer.json`, and copying their `.git` folders into the corresponding installed package directories. This will allow running `git diff` in-place to see local changes and prepare for upstream contributions.

---

## Requirements
- Parse `composer.json` to get all Drupal packages (type: drupal-module, drupal-profile) and their versions/sources.
- For each package:
  - Clone the upstream repository into a local `./repos` directory (e.g., `./repos/MODULENAME`)
  - Copy the `.git` folder from the cloned repo into the installed package location:
    - For modules: `docroot/modules/contrib/MODULENAME`
    - For profiles: `docroot/profiles/contrib/PROFILENAME`
  - Skip all other package types (themes, libraries, etc.)
- The script should not overwrite existing `.git` folders in the installed locations (warn or skip if present).
- The script should be idempotent and safe to re-run.

---

## Steps
1. **Parse composer.json**
   - Identify all packages of type `drupal-module` and `drupal-profile`.
   - Determine the source repository for each package (preferably from `composer.lock` for exact commit/version).

2. **Clone Repositories**
   - For each identified package, clone the upstream repository into `./repos/MODULENAME` or `./repos/PROFILENAME`.
   - Checkout the correct version/commit if possible.

3. **Copy .git Folders**
   - Copy the `.git` folder from the cloned repo to the installed package location.
   - Only do this for `docroot/modules/contrib` and `docroot/profiles/contrib`.
   - Skip if a `.git` folder already exists in the target location.

4. **Verification**
   - Ensure that running `git diff` in the installed package directory shows the local changes against the upstream.

5. **Cleanup (Optional)**
   - Optionally provide a cleanup script to remove `.git` folders from installed locations after use.

---

## Step 1: Clone and Prepare for Diff
- ✅ Script to clone all Drupal packages and copy their .git folders into installed locations.
- ✅ Interactive confirmations and --yes option for automation.
- ✅ --debug and --dry-run options for safe and verbose operation.

## Step 2: Analyze Git Changes in All Packages
- ✅ Script to scan all module/profile folders with .git, run git status/diff, and support interactive review.
- ✅ Progress output and compact status/diff for easier review.
- ✅ Only writable packages (where user has push access) are presented for further actions.
- ✅ Robust token loading from .env in both scripts/ and project root.
- ✅ Direct access check mode (--access-check) for any repo URL.
- ✅ Debug output for token loading and API checks.

## Step 3: Next Steps
- 🟡 Add per-package actions: select files to commit, create branch, push, skip, etc. (interactive menu)
- ⬜️ Summarize/report results, optionally output to file.
- ⬜️ Add cleanup script for removing .git folders from installed locations.
- ⬜️ Further UX improvements as needed.

---

## Notes
- All major technical blockers for access detection and interactive review are resolved.
- The workflow is now robust for real-world use and further extension.
- This process is for development and release preparation only. Do not commit `.git` folders to your main repository.
- Consider edge cases: forked modules, custom patches, or modules without public repos.
- Document any manual steps required for exceptions. 