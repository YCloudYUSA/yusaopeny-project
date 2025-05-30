# Drupal 11 Upgrade Plan for YMCA Website Services Distribution

This checklist tracks the steps required to upgrade the codebase to Drupal 11 (11.0.x) and properly release and maintain all related components.

---

## Legend
- ⬜️ **To Do**
- ✅ **Done**
- ⏸️ **Postponed**
- ⛔️ **Blocked**

---

## Upgrade Checklist

### 1. Codebase Preparation
- ⬜️ Review and apply code tree changes to all custom/contrib components in the project:
  - ⬜️ Apply all manually patched files
  - ⬜️ Remove outdated or incompatible Drupal configs
  - ⬜️ Update all `*.info.yml` files to support Drupal 11 version constraints
  - ⬜️ Identify and document all modules that were manually git cloned (e.g., `video_embed_field`)

### 2. Upstream Contribution & Releases
- ⬜️ Push all local changes to the respective upstream repositories:
  - ⬜️ Push to [drupal.org](https://www.drupal.org/) for Drupal modules
  - ⬜️ Push to [github.com](https://github.com/) for custom or external modules
- ⬜️ Release new versions/tags for all updated modules and components

### 3. Composer Management
- ⬜️ Move all manually added/overridden module versions to the top-level `composer.json` (root), overriding outdated versions as needed
- ⬜️ Remove any version constraints or overrides from sub-profiles or submodules that should be managed at the root
- ⬜️ Validate the `composer.json` for syntax and compatibility with Drupal 11

### 4. Branch & Release Management
- ⬜️ Push the updated `composer.json` and related code to the `drupal11lenient` branch of the main package repository
- ⬜️ Tag or release the branch as appropriate for distribution

### 5. Testing & Verification
- ⬜️ Run full site install and update tests on Drupal 11.0.x
- ⬜️ Verify all custom/contrib modules function as expected
- ⬜️ Document any issues, blockers, or postponed items

---

## Notes
- Use this checklist to track progress and update states as work proceeds.
- Add additional steps or sub-tasks as needed for your specific project structure.
