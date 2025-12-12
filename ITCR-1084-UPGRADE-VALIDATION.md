# ITCR-1084 Upgrade Path Validation

Validate the entity_browser → media_library migration upgrade path using all 33 PRs/MRs feature branches.

## Reference

- **Issue:** https://github.com/YCloudYUSA/yusaopeny_docs/issues/138
- **PR/MR List:** https://github.com/YCloudYUSA/yusaopeny_docs/issues/138#issuecomment-3642216229

## Prerequisites

- DDEV installed
- PHP 8.3+
- Database backup from existing YMCA Website Services site (.sql or .sql.gz)

## Setup

### 1. Create project directory

```bash
mkdir itcr-1084-validation && cd itcr-1084-validation
```

### 2. Copy composer.itcr-1084.json

```bash
cp composer.itcr-1084.json composer.json
```

### 3. Install dependencies

```bash
composer install
```

### 4. Configure DDEV

```bash
ddev config --project-type=drupal --docroot=docroot --php-version=8.3
ddev start
```

### 5. Import database

```bash
ddev import-db --file=/path/to/your-database.sql.gz
```

### 6. Run database updates

```bash
ddev drush updatedb -y
```

## Validation Checklist

After running updates, verify:

- [ ] All update hooks execute without errors
- [ ] Media Library widget visible on all media reference fields
- [ ] No `entity_browser` entries in active configuration (`drush cget` or config export)
- [ ] No JavaScript errors when editing content with media fields
- [ ] `entity_browser` module can be uninstalled: `ddev drush pmu entity_browser -y`

## Included Feature Branches

### GitHub Repositories (4)

| Package | Branch |
|---------|--------|
| ycloudyusa/yusaopeny | feature/ITCR-1084-deprecate-entity-browser |
| open-y-subprojects/openy_features | feature/ITCR-1084-deprecate-entity-browser |
| ycloudyusa/y_lb | feature/ITCR-1084-deprecate-entity-browser |
| ycloudyusa/lb_claro | feature/ITCR-1084-deprecate-entity-browser |

### Drupal.org Modules (28)

All using branch `ITCR-1084-deprecate-entity-browser`:

<details>
<summary>Click to expand full list</summary>

- drupal/lb_accordion
- drupal/lb_branch_amenities_blocks
- drupal/lb_cards
- drupal/lb_carousel
- drupal/lb_grid_cta
- drupal/lb_hero
- drupal/lb_modal
- drupal/lb_partners_blocks
- drupal/lb_ping_pong
- drupal/lb_promo
- drupal/lb_related_articles_blocks
- drupal/lb_related_events_blocks
- drupal/lb_simple_menu
- drupal/lb_staff_members_blocks
- drupal/lb_statistics
- drupal/lb_testimonial_blocks
- drupal/lb_webform
- drupal/ws_colorway_canada
- drupal/ws_event
- drupal/ws_lb_tabs
- drupal/ws_promotion
- drupal/ws_small_y
- drupal/y_camp
- drupal/y_donate
- drupal/y_facility
- drupal/y_lb_article
- drupal/y_program
- drupal/y_program_subcategory

</details>

## Troubleshooting

### Update hook fails

Check specific module's `.install` file for the update_10001 or update_10002 function and verify the config exists in your database.

### entity_browser won't uninstall

Some configuration may still reference entity_browser. Export config and search:

```bash
ddev drush cex -y
grep -r "entity_browser" config/sync/
```

### Media Library widget not appearing

Verify the field widget was updated in form display config:

```bash
ddev drush cget core.entity_form_display.node.YOUR_CONTENT_TYPE.default content.YOUR_FIELD_NAME
```
