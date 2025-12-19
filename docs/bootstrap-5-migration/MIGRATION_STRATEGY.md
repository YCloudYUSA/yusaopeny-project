# Bootstrap 4 to 5 Migration Strategy for Y USA Open YMCA

| | |
|---|---|
| **Document Version** | 1.1 |
| **Date** | 19 December 2025 |
| **Status** | Planning Phase |
| **Phases** | 8 |

---

## Executive Summary

This document outlines a comprehensive strategy to upgrade from Bootstrap 4 to Bootstrap 5 across the Y USA Open YMCA distribution. The migration affects ~70 modules/themes and requires careful planning, testing, and phased rollout.

> [!IMPORTANT]
> **Key Findings:**
> - 1 theme (openy_carnation) using Bootstrap 4.4.1
> - 3 Activity Finder Vue apps using Bootstrap 4.6.1 + BootstrapVue 2
> - ~50+ Layout Builder modules using Bootstrap 4.4.1
> - 1 module (lb_accordion) **already migrated to Bootstrap 5.3.3** ✅

> [!CAUTION]
> **Critical dependency:** BootstrapVue 2 is **NOT** compatible with Bootstrap 5. This is the most significant challenge in the migration.

---

## Table of Contents

1. [Current State Analysis](#current-state-analysis)
2. [Common Bootstrap Usage Patterns](#common-bootstrap-usage-patterns)
3. [Key Breaking Changes](#key-breaking-changes)
4. [Migration Strategy](#migration-strategy)
5. [Risk Assessment](#risk-assessment)
6. [Timeline & Resources](#timeline--resources)
7. [Success Metrics](#success-metrics)
8. [Next Steps](#next-steps)

---

## Current State Analysis

### Components Using Bootstrap 4

#### **A. Primary Theme (Bootstrap 4.4.1)**
**Location:** `docroot/themes/contrib/openy_carnation`

- Direct dependency: `"bootstrap": "^4.4.1"`
- Imports Bootstrap in `src/scss/style.scss:5`
- Uses compiled `dist/bootstrap.bundle.min.js`
- Uses Popper.js v1.16.0 (devDependency)
- **Impact:** CRITICAL - Core theme affects entire site

**Files affected:**
- `package.json`
- `openy_carnation.libraries.yml:9`
- `src/scss/style.scss`
- 60+ SCSS component files
- 50+ Twig templates with Bootstrap classes

---

#### **B. Activity Finder System**
**Location:** `docroot/modules/contrib/yusaopeny_activity_finder`

**Critical Dependencies:**
- Bootstrap 4.6.1 (openy_af4_vue_app)
- Bootstrap-Vue 2.22.0 (all three Vue apps)
- BootstrapVue 2.1.0 via CDN
- Popper.js 1.16.0 via CDN

**Affected Applications:**
1. **Activity Finder 4** (`openy_af4_vue_app/`)
   - Vue 2.6.14 + Bootstrap 4.6.1 + BootstrapVue 2.22.0
   - Most modern implementation

2. **Activity Finder** (`openy_af_vue_app/`)
   - Vue 2.6.10 + BootstrapVue 2.22.0
   - Webpack 4 build

3. **Camp Finder** (`openy_cf_vue_app/`)
   - Vue 2.6.10 + BootstrapVue 2.22.0
   - Similar to Activity Finder

> [!WARNING]
> **Critical Issue:** BootstrapVue 2.x **ONLY** supports Bootstrap 4 and Vue 2. Not compatible with Bootstrap 5.

---

#### **C. Layout Builder Modules**

**Already Migrated to Bootstrap 5.3.3:** ✅
- `lb_accordion` - Uses Bootstrap 5.3.3 + @popperjs/core 2.11.8 + webpack 5

**Still on Bootstrap 4.4.1:** ❌
- lb_branch_amenities_blocks
- lb_branch_hours_blocks
- lb_branch_social_links_blocks
- lb_cards
- lb_carousel
- lb_grid_cta (+ submodule lb_grid_icon)
- lb_hero
- lb_modal
- lb_partners_blocks
- lb_ping_pong
- lb_promo
- lb_related_articles_blocks
- lb_related_events_blocks
- lb_simple_menu
- lb_staff_members_blocks
- lb_statistics
- lb_table
- lb_testimonial_blocks
- lb_webform
- lb_activity_finder (submodule)

**Total:** 19 modules + 1 already migrated = 20 lb_* modules

---

#### **D. Website Services Modules (Bootstrap 4.4.1)**

**ws_small_y Suite** (15 submodules):
- small_y_accordions
- small_y_alerts
- small_y_articles
- small_y_branch
- small_y_cards
- small_y_carousels
- small_y_editor
- small_y_events
- small_y_hero
- small_y_icon_grid
- small_y_ping_pongs
- small_y_search
- small_y_tabs
- ws_small_y_staff
- ws_small_y_statistics
- ws_small_y_testimonials

**Other WS Modules:**
- ws_colorway_canada (+ submodule lb_hero_canada)
- ws_event
- ws_lb_tabs
- ws_promotion (+ submodules: ws_promotion_modal, ws_promotion_activity_finder)
- ws_home_branch (submodule of openy_custom/openy_home_branch)

---

#### **E. Y Content Type Modules (Bootstrap 4.4.1)**
- y_branch
- y_branch_menu
- y_camp
- y_donate (+ submodule lb_donate)
- y_facility
- y_lb (+ submodule y_lb_main_menu_cta_block)
- y_lb_article
- y_program
- y_program_subcategory

---

#### **F. Other Bootstrap-Dependent Modules**
- openy_node_alert (Bootstrap 4.4.1)
- openy_repeat (+ lb_repeat_schedules) - Bootstrap 4.4.1
- openy_map/openy_map_lb (Bootstrap 4.4.1)
- openy_custom/openy_calc (Bootstrap 4.4.1)
- bootstrap_layout_builder - Uses Bootstrap via SCSS, no npm dependency
- bootstrap_styles - Uses Bootstrap via SCSS, no npm dependency
- webform_bootstrap - Drupal contrib module

**Special Dependencies:**
- openy_repeat uses bootstrap-datepicker 1.3.0 via CDN
- editor_advanced_link uses bootstrap-buttons CSS
- y_pef_schedule/fullcalendar-app

**Total Affected Components:** ~70+ modules/themes

---

## Common Bootstrap Usage Patterns

### 1. SCSS Patterns

**Direct Bootstrap Import:**
```scss
// src/scss/style.scss
@import "bootstrap";  // From node_modules
```

**Bootstrap Utilities Used:**
- Grid: `.col-*`, `.row`, `.container`, `.container-fluid`, `.no-gutters`
- Components: `.btn-*`, `.modal-*`, `.card-*`, `.nav-*`, `.navbar-*`, `.dropdown-*`
- Forms: `.form-control`, `.form-group`, `.custom-select`, `.input-group-*`
- Utilities: `.alert-*`, `.text-*`, `.d-*`, `.m-*`, `.p-*`

**Custom Overrides:**
- `_init.scss` - Bootstrap variable overrides
- `_overrides.scss` - Component customizations
- `_variables.scss` - Theme-specific variables

**Files with Bootstrap Classes (20+ found):**
```
src/scss/modules/_columns.scss
src/scss/modules/_dialog.scss
src/scss/modules/_forms.scss
src/scss/modules/_menu.scss
src/scss/modules/_mobile-menu.scss
src/scss/modules/_af4.scss
src/scss/component/_filter.scss
...and more
```

---

### 2. Template Patterns (Twig)

**Bootstrap 4 Data Attributes Found:**
```twig
{# menu--main--primary-menu.html.twig:45 #}
{{ link(item_title, item.url, {'data-toggle': 'dropdown'}) }}
```

**Bootstrap 4 Classes Found:**
```twig
{# Found in 9 template files #}
.btn-block
.media-body
.close
.form-group
.custom-select
.input-group-append
.input-group-prepend
```

**Affected Template Types:**
- Navigation menus (primary, mobile)
- Alert blocks (header, footer)
- Form elements
- Repeating schedules
- FAQ items
- Node displays

---

### 3. JavaScript Patterns

**jQuery-Based Bootstrap Initialization:**
```javascript
// Found in openy_carnation_mobile.js, openy_carnation.js
$('.modal').modal();
$('.dropdown').dropdown();
$('.collapse').collapse();
$('.tooltip').tooltip();
$('.popover').popover();
$('.tab').tab();
$('.carousel').carousel();
```

**Popper.js v1.x Usage:**
- Required by Bootstrap 4 dropdowns, tooltips, popovers
- Must upgrade to @popperjs/core v2.x

---

### 4. Vue.js Patterns (Activity Finder)

**BootstrapVue 2.x Components:**
```vue
<b-button variant="primary">Click Me</b-button>
<b-modal id="modal-1">Modal Content</b-modal>
<b-card title="Card Title">Card Content</b-card>
<b-form-input v-model="text"></b-form-input>
```

**BootstrapVue CDN Integration:**
```yaml
# openy_activity_finder.libraries.yml:79-88
bootstrap-vue:
  version: 2.1.0
  js:
    https://cdn.jsdelivr.net/npm/bootstrap-vue@2.1.0/dist/bootstrap-vue.min.js: { type: external }
  css:
    https://cdn.jsdelivr.net/npm/bootstrap-vue@2.1.0/dist/bootstrap-vue.min.css: { type: external }
```

---

## Key Breaking Changes: Bootstrap 4 → 5

### Critical Changes

#### 1. Dependencies
- ❌ **jQuery REMOVED** - Bootstrap 5 no longer requires jQuery
- ✅ Popper v1.x → **@popperjs/core v2.x** (required)
- Dart Sass required (Libsass deprecated)

**Migration:**
```json
// package.json changes
{
  "dependencies": {
    "bootstrap": "^5.3.3",  // was ^4.4.1
    "@popperjs/core": "^2.11.8"  // was popper.js ^1.16.0
  }
}
```

---

#### 2. Data Attributes (ALL CHANGED)

**All data attributes now prefixed with `data-bs-*`:**

| Bootstrap 4 | Bootstrap 5 |
|-------------|-------------|
| `data-toggle` | `data-bs-toggle` |
| `data-target` | `data-bs-target` |
| `data-dismiss` | `data-bs-dismiss` |
| `data-slide` | `data-bs-slide` |
| `data-ride` | `data-bs-ride` |
| `data-parent` | `data-bs-parent` |

**Example:**
```html
<!-- Bootstrap 4 -->
<button data-toggle="modal" data-target="#myModal">Open</button>

<!-- Bootstrap 5 -->
<button data-bs-toggle="modal" data-bs-target="#myModal">Open</button>
```

---

#### 3. Grid System Changes

**New Breakpoint:**
- Added `xxl` breakpoint: 1400px+
- `.col-xxl-*` classes now available

**Gutter Changes:**
- Gutters: 30px → 1.5rem (~24px)
- `.no-gutters` → `.g-0`
- New gutter utilities: `.g-*`, `.gx-*`, `.gy-*`

**Removed:**
- `.form-row` (use `.row .g-*` instead)

**Columns:**
- No longer have `position: relative` by default
- May need to add `.position-relative` explicitly

**Order Classes:**
- Reduced from many `.order-*` to only `.order-0` through `.order-5`

---

#### 4. Components Removed/Changed

**Removed Components:**
- `.media` component → Use flexbox utilities
- `.jumbotron` → Custom styles or utilities
- `.card-deck` → Grid system
- `.text-hide` → Custom solution
- `.embed-responsive` → Ratio utilities

**Button Changes:**
- `.btn-block` → Use `.d-grid` wrapper with `.gap-*` utility
```html
<!-- Bootstrap 4 -->
<button class="btn btn-primary btn-block">Full Width</button>

<!-- Bootstrap 5 -->
<div class="d-grid gap-2">
  <button class="btn btn-primary">Full Width</button>
</div>
```

**Close Button:**
- `.close` → `.btn-close`
```html
<!-- Bootstrap 4 -->
<button type="button" class="close" data-dismiss="alert">&times;</button>

<!-- Bootstrap 5 -->
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
```

---

#### 5. Form Changes

**Major Form Class Changes:**

| Bootstrap 4 | Bootstrap 5 |
|-------------|-------------|
| `.form-group` | Removed (use margin utilities) |
| `.custom-select` | `.form-select` |
| `.custom-file` | Removed (native file input styled) |
| `.custom-range` | `.form-range` |
| `.custom-control` | `.form-check` |
| `.custom-checkbox` | `.form-check` |
| `.custom-radio` | `.form-check` |
| `.custom-switch` | `.form-check .form-switch` |
| `.input-group-append` | Removed (simplified) |
| `.input-group-prepend` | Removed (simplified) |

**Form Group Example:**
```html
<!-- Bootstrap 4 -->
<div class="form-group">
  <label for="email">Email</label>
  <input type="email" class="form-control" id="email">
</div>

<!-- Bootstrap 5 -->
<div class="mb-3">
  <label for="email" class="form-label">Email</label>
  <input type="email" class="form-control" id="email">
</div>
```

**Input Group Example:**
```html
<!-- Bootstrap 4 -->
<div class="input-group">
  <div class="input-group-prepend">
    <span class="input-group-text">@</span>
  </div>
  <input type="text" class="form-control">
</div>

<!-- Bootstrap 5 -->
<div class="input-group">
  <span class="input-group-text">@</span>
  <input type="text" class="form-control">
</div>
```

---

#### 6. Table Changes

**Class Changes:**
- `.thead-light` → `.table-light` (on `<thead>` or `<tr>`)
- `.thead-dark` → `.table-dark` (on `<thead>` or `<tr>`)
- Nested tables no longer inherit styles

---

#### 7. Typography Changes

**Links:**
- Links are underlined by default (not just on hover)
- Exception: Links in specific components (nav, navbar, etc.)

**Utilities:**
- `.text-*` utilities no longer add hover/focus states to links
- `.font-weight-*` → `.fw-*`
- `.font-italic` → `.fst-italic`
- `.text-left` → `.text-start`
- `.text-right` → `.text-end`

**Display Typography:**
- Overhauled display sizes
- `$display-*` variables → `$display-font-sizes` Sass map
- Added `.display-5` and `.display-6`

---

#### 8. Utility Class Changes

**Spacing Direction:**
| Bootstrap 4 | Bootstrap 5 |
|-------------|-------------|
| `.ml-*` | `.ms-*` (margin-start) |
| `.mr-*` | `.me-*` (margin-end) |
| `.pl-*` | `.ps-*` (padding-start) |
| `.pr-*` | `.pe-*` (padding-end) |
| `.left` | `.start` |
| `.right` | `.end` |

**Removed:**
- `.float-left` → `.float-start`
- `.float-right` → `.float-end`
- `.border-left` → `.border-start`
- `.border-right` → `.border-end`
- `.rounded-left` → `.rounded-start`
- `.rounded-right` → `.rounded-end`

---

#### 9. Colors & Accessibility

**Contrast Ratio:**
- 3:1 → 4.5:1 (WCAG 2.2 AA)

**Color Updates:**
- All colors updated for better accessibility
- Color contrast validated against WCAG standards

---

#### 10. JavaScript API Changes

**No More jQuery:**
```javascript
// Bootstrap 4 (jQuery)
$('#myModal').modal('show');

// Bootstrap 5 (Vanilla JS)
const myModal = new bootstrap.Modal(document.getElementById('myModal'));
myModal.show();
```

**Module Imports:**
```javascript
// Bootstrap 5 - Import specific components
import { Modal, Dropdown, Tooltip } from 'bootstrap';

// Or use the bundle
import 'bootstrap';
```

---

## Migration Strategy

### Phase 1: Preparation & Research

#### 1.1 Study Reference Implementation
- ✅ Analyze **lb_accordion** module (already migrated to Bootstrap 5.3.3)
- Document all changes made in lb_accordion
- Extract reusable patterns and scripts
- Create template migration checklist

**Files to review:**
```
docroot/modules/contrib/lb_accordion/
├── package.json (Bootstrap 5.3.3, @popperjs/core 2.11.8)
├── webpack.config.js (Webpack 5)
├── scss/ (Updated Bootstrap 5 syntax)
└── templates/ (Updated data attributes)
```

#### 1.2 Set Up Testing Environment
```bash
# Create migration branch
git checkout -b feature/bootstrap-5-migration

# Set up visual regression testing
npm install -D backstopjs
npx backstop init

# Set up accessibility testing
npm install -D pa11y-ci
# Create .pa11yci.json config

# Set up testing documentation
mkdir -p docs/bootstrap-5-migration/testing
```

**Testing Baseline:**
- Screenshot all pages/components (BackstopJS)
- Run accessibility audit (Pa11y)
- Document all Bootstrap-dependent features
- Create test matrix spreadsheet

#### 1.3 Dependency Analysis
```bash
# Scan all Bootstrap dependencies
npm list bootstrap --all > docs/bootstrap-5-migration/bootstrap-dependencies.txt

# Scan all bootstrap-vue dependencies
npm list bootstrap-vue --all > docs/bootstrap-5-migration/bootstrap-vue-dependencies.txt

# Scan Popper.js dependencies
npm list popper.js --all > docs/bootstrap-5-migration/popper-dependencies.txt

# Find all data-toggle usage
grep -r "data-toggle" docroot/ > docs/bootstrap-5-migration/data-toggle-usage.txt

# Find deprecated classes
grep -r "btn-block\|form-group\|custom-select\|media-body\|close" docroot/ \
  > docs/bootstrap-5-migration/deprecated-classes.txt
```

#### 1.4 Vue.js Migration Research

**Critical Decision Point:** BootstrapVue 2 is NOT compatible with Bootstrap 5

**Research Tasks:**
- [ ] Audit all BootstrapVue component usage in Activity Finder apps
- [ ] Evaluate BootstrapVueNext (currently alpha) stability
- [ ] Research alternative approaches:
  - Option A: Migrate to BootstrapVueNext + Vue 3
  - Option B: Use Bootstrap 5 directly (vanilla JS)
  - Option C: Keep Activity Finder isolated on Bootstrap 4 (temporary)
- [ ] Create proof-of-concept for chosen approach
- [ ] Document decision with pros/cons

**Deliverables:**
- [ ] Decision document: `docs/bootstrap-5-migration/decisions/activity-finder-approach.md`
- [ ] POC code (if applicable)
- [ ] Timeline estimate for Activity Finder migration

---

### Phase 2: Core Theme Migration

**Priority:** CRITICAL - Theme affects entire site

#### 2.1 Update openy_carnation Dependencies

```bash
cd docroot/themes/contrib/openy_carnation

# Update package.json
npm install bootstrap@^5.3.3 @popperjs/core@^2.11.8 --save

# Update devDependencies
npm install webpack@^5.91.0 webpack-cli@^5.1.4 --save-dev
npm install sass@^1.75.0 sass-loader@^14.2.0 --save-dev
npm install css-loader@^7.1.1 mini-css-extract-plugin@^2.8.1 --save-dev

# Remove old dependencies
npm uninstall popper.js

# Install
npm install
```

#### 2.2 Update SCSS Files

**Step 1: Update Bootstrap Variable Overrides**
```scss
// src/scss/_init.scss
// Review and update Bootstrap 5 variable names
// Reference: https://getbootstrap.com/docs/5.3/customize/sass/

// Common variable name changes:
// $enable-rounded → still $enable-rounded ✓
// $enable-shadows → still $enable-shadows ✓
// $enable-gradients → still $enable-gradients ✓
// $grid-gutter-width → $spacer * 1.5 (24px instead of 30px)
```

**Step 2: Update Component Overrides**
```scss
// src/scss/_overrides.scss
// Replace deprecated classes:
// .close → .btn-close
// .media → flexbox utilities
// .text-left → .text-start
// .text-right → .text-end
// .ml-* → .ms-*
// .mr-* → .me-*
```

**Step 3: Update Component Files (60+ files)**
```bash
# Find files using deprecated classes
grep -r "\.close\|\.media\|\.text-left\|\.text-right\|\.ml-\|\.mr-" src/scss/

# Update each file systematically
# Document changes in migration log
```

**Files to update:**
```
src/scss/modules/_af4.scss
src/scss/modules/_header.scss
src/scss/modules/_menu.scss
src/scss/modules/_mobile-menu.scss
src/scss/modules/_forms.scss
src/scss/modules/_dialog.scss
src/scss/component/_buttons.scss
src/scss/component/_form.scss
src/scss/component/_alert.scss
... (50+ more files)
```

#### 2.3 Update Templates (Twig)

**Data Attribute Updates:**
```bash
# Run automated find/replace
find templates/ -name "*.twig" -type f -exec sed -i.bak \
  -e 's/data-toggle="/data-bs-toggle="/g' \
  -e 's/data-target="/data-bs-target="/g' \
  -e 's/data-dismiss="/data-bs-dismiss="/g' \
  -e 's/data-slide="/data-bs-slide="/g' \
  -e 's/data-parent="/data-bs-parent="/g' \
  {} +

# Review changes
git diff templates/
```

**Class Name Updates:**
```bash
# Update deprecated classes
find templates/ -name "*.twig" -type f -exec sed -i.bak \
  -e 's/"close"/"btn-close"/g' \
  -e 's/"text-left"/"text-start"/g' \
  -e 's/"text-right"/"text-end"/g' \
  {} +
```

**Manual Review Required:**
- `btn-block` → needs wrapper div with `.d-grid`
- `form-group` → use margin utilities instead
- `custom-select` → `form-select`
- `media` component → flexbox utilities

**Templates requiring manual updates:**
```
templates/menu/menu--main--primary-menu.html.twig
templates/menu/mobile/menu--main--mobile-menu.html.twig
templates/page/page.html.twig
templates/node/facility/node--facility--full.html.twig
templates/form/form-element.html.twig
templates/node/alert/*.html.twig
templates/modules/openy-faq-item.html.twig
templates/openy_calc/openy-calc-form-header.html.twig
templates/openy_repeat/openy-repeat-schedule-dashboard--sidebar.html.twig
```

#### 2.4 Update JavaScript

**Update Bootstrap Initialization:**
```javascript
// dist/js/openy_carnation.js
// dist/js/openy_carnation_mobile.js

// Bootstrap 4 (jQuery)
$('.modal').modal();
$('.dropdown').dropdown();

// Bootstrap 5 (Vanilla JS)
import { Modal, Dropdown, Collapse, Tooltip } from 'bootstrap';

// Initialize modals
document.querySelectorAll('.modal').forEach(el => {
  new Modal(el);
});

// Initialize dropdowns
document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => {
  new Dropdown(el);
});
```

> [!NOTE]
> Keep jQuery for Drupal compatibility, but remove Bootstrap jQuery dependency

#### 2.5 Update Libraries

```yaml
# openy_carnation.libraries.yml
global-styling:
  version: 1.11  # Increment version
  css:
    base:
      //cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.css: {type: external}
      dist/css/style.css: {}
  js:
    dist/main.js: {}
    dist/bootstrap.bundle.min.js: {}  # Bootstrap 5 bundle includes Popper
    dist/js/openy_carnation.js: {}
    # ... rest of JS files
  dependencies:
    - core/jquery  # Keep for Drupal
    - core/jquery.ui.datepicker
    - core/once
    - core/drupal
```

#### 2.6 Build and Test

```bash
# Build theme
npm run build

# Clear Drupal cache
fin drush cr  # or drush cr

# Visual regression test
npx backstop test

# Accessibility test
npx pa11y-ci

# Manual testing checklist:
# - Homepage
# - All content types (Branch, Program, Class, Event, News, Blog, Landing)
# - All paragraph types
# - Navigation (desktop & mobile)
# - Forms
# - Modals
# - Alerts
# - Responsive breakpoints (sm, md, lg, xl, xxl)
```

#### 2.7 Documentation

```markdown
# Create migration log
docs/bootstrap-5-migration/THEME_MIGRATION_LOG.md

Document:
- All changes made
- Issues encountered
- Workarounds applied
- Testing results
- Known issues
```

---

### Phase 3: Layout Builder Modules

**Priority:** HIGH - Used extensively across sites

#### 3.1 Migration Order

**Reference Implementation:** lb_accordion (already migrated)

**Batch 1: High Priority**
1. lb_hero - Homepage hero component
2. lb_cards - Commonly used card layouts
3. lb_carousel - Image carousels
4. lb_modal - Modal dialogs
5. lb_webform - Form integration

**Batch 2: Medium Priority**
6. lb_branch_amenities_blocks
7. lb_branch_hours_blocks
8. lb_branch_social_links_blocks
9. lb_ping_pong - Alternating content blocks
10. lb_promo - Promotional blocks

**Batch 3: Lower Priority**
11. lb_grid_cta (+ lb_grid_icon submodule)
12. lb_partners_blocks
13. lb_related_articles_blocks
14. lb_related_events_blocks
15. lb_simple_menu
16. lb_staff_members_blocks
17. lb_statistics
18. lb_table
19. lb_testimonial_blocks

#### 3.2 Standard Migration Process (per module)

**Step 1: Study lb_accordion Implementation**
```bash
cd docroot/modules/contrib/lb_accordion

# Review changes:
- package.json (Bootstrap 5.3.3)
- webpack.config.js (Webpack 5)
- SCSS files (Bootstrap 5 syntax)
- Templates (data-bs-* attributes)
```

**Step 2: Update Dependencies**
```bash
cd docroot/modules/contrib/[MODULE_NAME]

# Update package.json
npm install bootstrap@^5.3.3 @popperjs/core@^2.11.8 --save

# Update build tools (follow lb_accordion pattern)
npm install webpack@^5.91.0 webpack-cli@^5.1.4 --save-dev
npm install sass@^1.75.0 sass-loader@^14.2.0 --save-dev
npm install css-loader@^7.1.1 style-loader@^4.0.0 --save-dev
npm install mini-css-extract-plugin@^2.8.1 --save-dev
npm install purgecss-webpack-plugin@^6.0.0 --save-dev

# Install
npm install
```

**Step 3: Update Webpack Config**
```javascript
// webpack.config.js
// Copy pattern from lb_accordion

const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { PurgeCSSPlugin } = require('purgecss-webpack-plugin');

module.exports = {
  mode: 'production',
  entry: './scss/style.scss',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'bundle.js'
  },
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'sass-loader'
        ]
      }
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: 'style.css'
    }),
    new PurgeCSSPlugin({
      // PurgeCSS config
    })
  ]
};
```

**Step 4: Update SCSS**
```bash
# Review and update Bootstrap 5 syntax
# Update deprecated classes
# Test responsive behavior
```

**Step 5: Update Templates**
```bash
# Update data-* attributes
find templates/ -name "*.twig" -exec sed -i.bak \
  -e 's/data-toggle="/data-bs-toggle="/g' \
  {} +

# Update class names manually where needed
```

**Step 6: Build and Test**
```bash
npm run build

# Test the module
# - Visual appearance
# - Interactive behavior
# - Responsive design
# - Accessibility
```

**Step 7: Document**
```bash
# Update module README.md
# Document any breaking changes
# Add to migration log
```

#### 3.3 Special Cases

**lb_modal:**
- Bootstrap 5 modal API changed significantly
- Update JavaScript initialization
- Test backdrop behavior
- Test keyboard navigation (ESC key)

**lb_webform:**
- Coordinate with webform_bootstrap module
- Test all form element types
- Test form validation
- Test AJAX submission

**lb_carousel:**
- JavaScript API changes
- Test touch swipe on mobile
- Test keyboard controls
- Test auto-play behavior

**lb_table:**
- Test nested tables (no longer inherit styles)
- Test responsive tables
- Test table variants (.table-striped, .table-hover)

---

### Phase 4: Activity Finder Migration

**Priority:** CRITICAL - Complex, high-impact component

> [!CAUTION]
> **Major Challenge:** BootstrapVue 2 is NOT compatible with Bootstrap 5. This phase requires the most careful planning.

#### 4.1 Decision Matrix

See `docs/bootstrap-5-migration/decisions/ACTIVITY_FINDER_DECISION.md` for detailed analysis.

**Options:**

| Option | Description | Risk | Recommendation |
|--------|-------------|----------|------|----------------|
| **A. BootstrapVueNext** | Migrate to Bootstrap 5 + Vue 3 | HIGH (alpha software) | Future consideration |
| **B. Bootstrap 5 Direct** | Remove BootstrapVue, use Bootstrap 5 vanilla JS | MEDIUM | ✅ Recommended |
| **C. Temporary Isolation** | Scope Bootstrap 4 CSS, delay migration | LOW | Short-term solution |
| **D. Keep Bootstrap 4** | No changes | Ongoing | LOW | Not recommended |

**Recommended Approach:** **Option C → Option B (Phased)**
1. Start with temporary isolation (Option C)
2. Migrate incrementally to Bootstrap 5 direct (Option B)

#### 4.2 Phase 4A: Temporary Isolation

**Goal:** Allow theme to use Bootstrap 5 while Activity Finder stays on Bootstrap 4

**Strategy:** Scope Bootstrap 4 CSS to Activity Finder components only

```scss
// openy_af4_vue_app/scss/activity-finder.scss

// Wrap Bootstrap 4 in a namespace
.activity-finder-wrapper {
  @import "bootstrap-4";  // Keep local copy of Bootstrap 4

  // All Activity Finder styles scoped here
}
```

```yaml
# openy_activity_finder.libraries.yml
activity_finder_4:
  version: 4.66
  js:
    openy_af4_vue_app/dist/activity_finder_4.umd.min.js: { minified: true }
  css:
    theme:
      openy_af4_vue_app/dist/activity_finder_4_scoped.css: { minified: true }  # Scoped Bootstrap 4
  dependencies:
    - openy_system/polyfill
    - openy_system/vue
    - openy_system/vue-router
    - openy_system/axios
    # Remove bootstrap-vue CDN dependency
    - openy_system/openy_system.ajax_spinner
    - openy_system/openy_system.modal
```

**Template Update:**
```twig
{# templates/openy-activity-finder-4-block.html.twig #}
<div class="activity-finder-wrapper">  {# Scope Bootstrap 4 #}
  <div id="activity-finder-4" data-config="{{ config }}"></div>
</div>
```

**Testing:**
- Verify Activity Finder still works with Bootstrap 4
- Verify theme uses Bootstrap 5 without conflicts
- Test on same page where both are present

#### 4.3 Phase 4B: Incremental Migration

**Goal:** Replace BootstrapVue components with Bootstrap 5 vanilla JS

**Step 1: Audit BootstrapVue Usage (Week 1)**
```bash
cd docroot/modules/contrib/yusaopeny_activity_finder

# Find all BootstrapVue components used
grep -r "b-button\|b-modal\|b-card\|b-form\|b-dropdown\|b-navbar\|b-collapse" \
  openy_af4_vue_app/src/ > bootstrap-vue-components.txt

# Common BootstrapVue components likely used:
# - b-button
# - b-form-input
# - b-form-select
# - b-form-checkbox
# - b-form-radio
# - b-modal
# - b-card
# - b-dropdown
# - b-navbar
# - b-collapse
# - b-tabs
# - b-pagination
```

**Step 2: Create Bootstrap 5 Equivalents (Weeks 2-4)**

Create reusable Vue components that use Bootstrap 5:

```vue
<!-- components/Bootstrap5/Button.vue -->
<template>
  <button :class="buttonClasses" :type="type" @click="handleClick">
    <slot></slot>
  </button>
</template>

<script>
export default {
  name: 'Bs5Button',
  props: {
    variant: {
      type: String,
      default: 'primary'
    },
    type: {
      type: String,
      default: 'button'
    },
    size: String,
    block: Boolean
  },
  computed: {
    buttonClasses() {
      const classes = ['btn', `btn-${this.variant}`];
      if (this.size) classes.push(`btn-${this.size}`);
      if (this.block) classes.push('w-100');  // Bootstrap 5: no more btn-block
      return classes;
    }
  },
  methods: {
    handleClick(event) {
      this.$emit('click', event);
    }
  }
}
</script>
```

```vue
<!-- components/Bootstrap5/Modal.vue -->
<template>
  <div class="modal fade" :id="id" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" :class="modalDialogClasses">
      <div class="modal-content">
        <div class="modal-header" v-if="hasHeader">
          <h5 class="modal-title">{{ title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <slot></slot>
        </div>
        <div class="modal-footer" v-if="hasFooter">
          <slot name="footer"></slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Modal } from 'bootstrap';

export default {
  name: 'Bs5Modal',
  props: {
    id: {
      type: String,
      required: true
    },
    title: String,
    size: String,
    hasHeader: {
      type: Boolean,
      default: true
    },
    hasFooter: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      modalInstance: null
    }
  },
  computed: {
    modalDialogClasses() {
      const classes = [];
      if (this.size) classes.push(`modal-${this.size}`);
      return classes;
    }
  },
  mounted() {
    // Initialize Bootstrap 5 modal
    this.modalInstance = new Modal(this.$el);
  },
  beforeUnmount() {
    if (this.modalInstance) {
      this.modalInstance.dispose();
    }
  },
  methods: {
    show() {
      this.modalInstance?.show();
    },
    hide() {
      this.modalInstance?.hide();
    }
  }
}
</script>
```

**Step 3: Replace BootstrapVue Components (Weeks 5-8)**

```vue
<!-- Before (BootstrapVue 2) -->
<template>
  <div>
    <b-button variant="primary" @click="openModal">Open Modal</b-button>

    <b-modal id="modal-1" title="My Modal" hide-footer>
      <b-form @submit="onSubmit">
        <b-form-input v-model="text" placeholder="Enter text"></b-form-input>
        <b-button type="submit" variant="primary">Submit</b-button>
      </b-form>
    </b-modal>
  </div>
</template>

<script>
export default {
  data() {
    return {
      text: ''
    }
  },
  methods: {
    openModal() {
      this.$bvModal.show('modal-1');
    },
    onSubmit(event) {
      event.preventDefault();
      // Handle submit
    }
  }
}
</script>
```

```vue
<!-- After (Bootstrap 5 Direct) -->
<template>
  <div>
    <Bs5Button variant="primary" @click="openModal">Open Modal</Bs5Button>

    <Bs5Modal ref="modal" id="modal-1" title="My Modal" :has-footer="false">
      <form @submit.prevent="onSubmit">
        <div class="mb-3">
          <input type="text" class="form-control" v-model="text" placeholder="Enter text">
        </div>
        <Bs5Button type="submit" variant="primary">Submit</Bs5Button>
      </form>
    </Bs5Modal>
  </div>
</template>

<script>
import Bs5Button from '@/components/Bootstrap5/Button.vue';
import Bs5Modal from '@/components/Bootstrap5/Modal.vue';

export default {
  components: {
    Bs5Button,
    Bs5Modal
  },
  data() {
    return {
      text: ''
    }
  },
  methods: {
    openModal() {
      this.$refs.modal.show();
    },
    onSubmit() {
      // Handle submit
    }
  }
}
</script>
```

**Step 4: Update Dependencies**
```json
// openy_af4_vue_app/package.json
{
  "dependencies": {
    "bootstrap": "^5.3.3",  // was ^4.6.1
    "@popperjs/core": "^2.11.8",
    // Remove bootstrap-vue
    "axios": "^0.27.2",
    "core-js": "^3.25.0",
    "vue": "^2.6.14",  // Keep Vue 2 for now
    "vue-router": "^3.5.3"
  }
}
```

**Step 5: Update Build Process**
```javascript
// openy_af4_vue_app/vue.config.js
// Import Bootstrap 5 JavaScript
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
```

**Step 6: Test Each App (Weeks 9-10)**

**Test Camp Finder:**
- Location selection
- Date picker (ensure v-calendar works)
- Filters
- Results display
- Session details
- Mobile responsive

**Test Activity Finder:**
- Program search
- Filters (location, category, age, day/time)
- Results grid/list view
- Session details
- Registration links

**Test Activity Finder 4:**
- Modern interface
- Advanced filters
- Results display
- Session details
- Add to calendar

#### 4.4 Vue 3 Migration (Optional - Future Phase)

> [!WARNING]
> Vue 2 reached End of Life on December 31, 2023. Consider Vue 3 migration as a future phase.

If migrating to Vue 3:
- Use Vue 3 Migration Build for gradual migration
- Update to Vue 3 syntax (Composition API optional)
- Update vue-router to v4
- Test thoroughly

**Resources:**
- [Vue 3 Migration Guide](https://v3-migration.vuejs.org/)

---

### Phase 5: Website Services & Y Modules

**Priority:** MEDIUM - Site-specific functionality

#### 5.1 ws_small_y Module Suite (15 submodules)

**Strategy:** Batch process similar modules

**Modules:**
1. small_y_accordions
2. small_y_alerts
3. small_y_articles
4. small_y_branch
5. small_y_cards
6. small_y_carousels
7. small_y_editor
8. small_y_events
9. small_y_hero
10. small_y_icon_grid
11. small_y_ping_pongs
12. small_y_search
13. small_y_tabs
14. ws_small_y_staff
15. ws_small_y_statistics
16. ws_small_y_testimonials

**Batch Migration Script:**
```bash
#!/bin/bash
# migrate-small-y-modules.sh

MODULES=(
  "small_y_accordions"
  "small_y_alerts"
  "small_y_articles"
  # ... etc
)

for module in "${MODULES[@]}"; do
  echo "Migrating $module..."
  cd "docroot/modules/contrib/ws_small_y/modules/$module"

  # Update dependencies
  npm install bootstrap@^5.3.3 @popperjs/core@^2.11.8 --save

  # Update build tools
  npm install webpack@^5.91.0 --save-dev

  # Install
  npm install

  # Build
  npm run build

  echo "$module migrated"
  cd -
done
```

**Testing Priority:**
1. High: small_y_hero, small_y_cards, small_y_carousels
2. Medium: small_y_accordions, small_y_tabs, small_y_alerts
3. Lower: Others

#### 5.2 Content Type Modules

**y_branch** (Branch content type)
- Update branch page templates
- Test amenities display
- Test hours display
- Test location map integration

**y_camp** (Camp content type)
- Update camp page templates
- Test camp quick links
- Test camp menu
- Integration with Camp Finder

**y_facility** (Facility content type)
- Update facility templates
- Test facility display

**y_program** (Program content type)
- Update program templates
- Test program display
- Integration with Activity Finder

**y_lb** (Layout Builder integration)
- Update LB-specific templates
- Test y_lb_main_menu_cta_block

#### 5.3 Other WS Modules

**ws_event** (Event management)
- Update event templates
- Test event display
- Test add-to-calendar functionality
- Test event info block

**ws_promotion** (Promotional content)
- Update promotion templates
- Test ws_promotion_modal
- Test ws_promotion_activity_finder integration

**ws_colorway_canada** (Canadian theme variant)
- Update Canadian-specific styles
- Test lb_hero_canada
- Coordinate with openy_carnation theme

**ws_lb_tabs** (Tab component)
- Update tab templates
- Test tab switching
- Test keyboard navigation

---

### Phase 6: Supporting Modules

**Priority:** LOW-MEDIUM

#### 6.1 Bootstrap-Related Drupal Contrib

**bootstrap_layout_builder**
- Uses Bootstrap via SCSS (no npm dependency)
- Should inherit Bootstrap 5 from theme
- Test Layout Builder interface
- Test all layout options

**bootstrap_styles**
- Uses Bootstrap via SCSS (no npm dependency)
- Should inherit Bootstrap 5 from theme
- Test style plugins
- Test background styles, spacing, border, etc.

**webform_bootstrap**
- Drupal contrib module
- Check for Bootstrap 5 compatible version on Drupal.org
- Update via composer if available
- Test all webform element types
- Test webform theming

#### 6.2 Date Picker Replacement

**Current:** bootstrap-datepicker 1.3.0 (Bootstrap 3/4 only)

**Options:**
1. **Flatpickr** (Recommended)
   - No Bootstrap dependency
   - Modern, lightweight
   - Good accessibility
   - Mobile-friendly

2. **Tempus Dominus** (Bootstrap 5 native)
   - Built for Bootstrap 5
   - Comprehensive features
   - Heavier weight

3. **Native HTML5 Date Input**
   - No dependencies
   - Good browser support
   - Limited styling options

**Migration:**
```yaml
# openy_repeat/openy_repeat.libraries.yml

# Remove bootstrap-datepicker
# bootstrap-datepicker:
#   js:
#     https://cdnjs.cloudflare.com/.../bootstrap-datepicker.min.js

# Add Flatpickr
flatpickr:
  version: 4.6.13
  js:
    https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js: { type: external }
  css:
    theme:
      https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css: { type: external }
```

#### 6.3 Other Dependencies

**openy_node_alert**
- Update alert templates
- Test alert display (header, footer)
- Test alert dismissal

**openy_repeat**
- Update repeat schedules
- Replace bootstrap-datepicker
- Test schedule dashboard

**openy_map/openy_map_lb**
- Update map block templates
- Test map display
- Test location markers

**openy_custom/openy_calc**
- Update calculator form
- Test calculator functionality

**openy_custom/openy_home_branch/ws_home_branch**
- Update home branch templates
- Test location selector

**y_pef_schedule/fullcalendar-app**
- Check FullCalendar compatibility
- Update if needed
- Test calendar display

---

### Phase 7: Testing & Quality Assurance

**Priority:** CRITICAL

#### 7.1 Automated Testing

**Visual Regression Testing (BackstopJS)**
```bash
# Install
npm install -g backstopjs

# Initialize
cd docs/bootstrap-5-migration/testing
backstop init

# Configure scenarios
# Edit backstop.json with all pages/components

# Create baseline (Bootstrap 4)
git checkout main
backstop reference

# Test migration (Bootstrap 5)
git checkout feature/bootstrap-5-migration
backstop test

# Review report
backstop openReport
```

**Accessibility Testing (Pa11y)**
```bash
# Install
npm install -g pa11y-ci

# Configure
# Create .pa11yci.json with all URLs

# Run tests
pa11y-ci

# Generate report
pa11y-ci --reporter html > pa11y-report.html
```

**Code Quality**
```bash
# Run PHPCS
./runsniffers.sh

# Auto-fix code style
./runcodestyleautofix.sh

# Run Behat tests
./runtests.sh
```

#### 7.2 Manual Testing Matrix

Create spreadsheet: `docs/bootstrap-5-migration/testing/TESTING_MATRIX.xlsx`

| Component | Desktop Chrome | Desktop Firefox | Desktop Safari | Mobile iOS | Mobile Android | Screen Reader | Notes |
|-----------|----------------|-----------------|----------------|------------|----------------|---------------|-------|
| **Pages** | | | | | | | |
| Homepage | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Branch page | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Program page | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Class page | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Event page | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| News/Blog | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Landing page | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| **Finders** | | | | | | | |
| Activity Finder | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Camp Finder | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Location Finder | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| **Components** | | | | | | | |
| Navigation (desktop) | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Navigation (mobile) | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Modals | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Alerts | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Forms | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Cards | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Carousels | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Accordions | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Tabs | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| **Schedules** | | | | | | | |
| Class schedules | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Event schedules | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| Group Ex Pro | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| **Breakpoints** | | | | | | | |
| sm (576px) | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| md (768px) | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| lg (992px) | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| xl (1200px) | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | |
| xxl (1400px) | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | NEW! |

#### 7.3 Browser Testing

**Desktop Browsers:**
- Chrome (latest stable)
- Firefox (latest stable)
- Safari (latest stable)
- Edge (latest stable)

**Mobile Browsers:**
- Mobile Safari (iOS 15+)
- Mobile Chrome (Android 10+)

**Screen Readers:**
- NVDA (Windows) + Chrome
- JAWS (Windows) + Chrome
- VoiceOver (macOS) + Safari
- VoiceOver (iOS) + Safari

#### 7.4 Performance Testing

**Lighthouse Audit:**
```bash
# Install Lighthouse
npm install -g lighthouse

# Run audit
lighthouse https://yusaopeny.docksal.site --output html --output-path report.html

# Target scores:
# - Performance: 90+
# - Accessibility: 95+
# - Best Practices: 90+
# - SEO: 90+
```

**Bundle Size Analysis:**
```bash
# Compare Bootstrap 4 vs Bootstrap 5 bundle sizes
# Bootstrap 4: ~150KB (CSS) + ~50KB (JS)
# Bootstrap 5: ~130KB (CSS) + ~45KB (JS)
# Expected savings: ~15-20%

# Analyze with webpack-bundle-analyzer
npm install -D webpack-bundle-analyzer
# Add to webpack config
# Run build and check report
```

**Performance Metrics:**
- First Contentful Paint (FCP): < 1.8s
- Time to Interactive (TTI): < 3.8s
- Largest Contentful Paint (LCP): < 2.5s
- Cumulative Layout Shift (CLS): < 0.1
- Total Blocking Time (TBT): < 300ms

#### 7.5 Accessibility Testing

**WCAG 2.2 AA Compliance:**
- [ ] Color contrast ratios ≥ 4.5:1 (normal text)
- [ ] Color contrast ratios ≥ 3:1 (large text, UI components)
- [ ] All interactive elements keyboard accessible
- [ ] Focus indicators visible
- [ ] Skip navigation links present
- [ ] ARIA labels where needed
- [ ] Form labels properly associated
- [ ] Alt text for images
- [ ] Heading hierarchy logical
- [ ] Tables have proper headers

**Automated Tools:**
- Pa11y-ci (command line)
- axe DevTools (browser extension)
- WAVE (browser extension)

**Manual Testing:**
- Keyboard navigation only (no mouse)
- Screen reader testing (NVDA, JAWS, VoiceOver)
- High contrast mode (Windows)
- Browser zoom to 200%

---

### Phase 8: Documentation & Rollout

#### 8.1 Documentation Updates

**Module READMEs:**
Update each migrated module's README.md:
- Bootstrap version used
- Build instructions
- Testing instructions
- Known issues
- Breaking changes from Bootstrap 4

**Project Documentation:**
```
docs/bootstrap-5-migration/
├── MIGRATION_STRATEGY.md (this file)
├── MIGRATION_LOG.md (detailed change log)
├── BREAKING_CHANGES.md (for site builders)
├── TESTING_RESULTS.md (QA summary)
├── decisions/
│   ├── ACTIVITY_FINDER_DECISION.md
│   └── DATE_PICKER_DECISION.md
├── sprints/
│   ├── SPRINT_1.md
│   ├── SPRINT_2.md
│   └── ...
└── testing/
    ├── TESTING_MATRIX.xlsx
    ├── backstop/
    └── pa11y/
```

**CLAUDE.md Update:**
Add Bootstrap 5 section:
```markdown
## Bootstrap 5 Migration (Drupal 11)

As part of the Drupal 11 migration, the project has been upgraded from Bootstrap 4 to Bootstrap 5.

### Key Changes
- Bootstrap 4.4.1 → Bootstrap 5.3.3
- jQuery removed from Bootstrap (still used by Drupal)
- BootstrapVue 2 replaced with Bootstrap 5 vanilla JS in Activity Finder
- All data-* attributes now prefixed with data-bs-*
- Many class name changes (see BREAKING_CHANGES.md)

### Building Themes/Modules
All modules now use Bootstrap 5. Follow the pattern in lb_accordion for reference.

### Resources
- Migration documentation: docs/bootstrap-5-migration/
- Bootstrap 5 docs: https://getbootstrap.com/docs/5.3/
```

**ds-docs.y.org Updates:**
- Create Bootstrap 5 migration guide
- Update theme documentation
- Update developer documentation
- Update site builder documentation

#### 8.2 Migration Guide for Site Builders

Create: `docs/bootstrap-5-migration/SITE_BUILDER_GUIDE.md`

**Contents:**
1. What's changing
2. Impact on existing sites
3. Upgrade process
4. Testing checklist
5. Troubleshooting
6. Getting help

**Key Messages:**
- Most changes are automatic
- Visual appearance should be the same
- Test your custom CSS/JS
- Report any issues

#### 8.3 Communication Plan

**Timeline:**

**Week -4:** Initial announcement
- "We're starting Bootstrap 5 migration"
- Timeline overview
- How to get involved (testing, feedback)

**Week -2:** Progress update
- "50% complete"
- Early testing available
- Call for beta testers

**Week 0:** Release announcement
- "Bootstrap 5 migration complete"
- Upgrade instructions
- Support resources

**Week +2:** Follow-up
- Collect feedback
- Address issues
- Celebrate success

**Channels:**
- GitHub Discussions
- Slack/Discord
- Email newsletter
- Documentation site
- Community calls

#### 8.4 Rollout Strategy

**Staged Rollout:**

**Stage 1: Internal Testing (Week 1-2)**
- Install on internal test sites
- Development team testing
- QA team testing
- Document issues
- Fix critical bugs

**Stage 2: Beta Testing (Week 3-4)**
- Select 5-10 partner organizations
- Volunteers with technical capacity
- Deploy Bootstrap 5 version
- Collect feedback
- Fix issues
- Update documentation

**Stage 3: Stable Release (Week 5-6)**
- Announce stable release
- Update main branch
- Create release notes
- Provide upgrade path
- Offer migration support

**Rollback Plan:**
- Keep Bootstrap 4 branch available
- Document rollback process
- Provide support for rollback if needed

---

## Risk Assessment & Mitigation

### High Risk Areas

#### 1. Activity Finder Breakage
**Risk:** Activity Finder stops working or has visual bugs
**Impact:** CRITICAL - Core functionality broken
**Probability:** HIGH
**Mitigation:**
- Phase 4A: Temporary isolation with scoped CSS
- Extensive testing with real data
- Staged rollout with beta testers
- Rollback plan ready
- Keep Bootstrap 4 version available

#### 2. Visual Regressions
**Risk:** Layout breaks, visual inconsistencies
**Impact:** HIGH - Poor user experience
**Probability:** MEDIUM
**Mitigation:**
- Automated visual regression testing (BackstopJS)
- Manual QA testing matrix
- Cross-browser testing
- Responsive testing at all breakpoints
- Fix issues before rollout

#### 3. jQuery Removal Issues
**Risk:** JavaScript functionality breaks
**Impact:** HIGH - Interactive features broken
**Probability:** MEDIUM
**Mitigation:**
- Keep jQuery for Drupal compatibility
- Incremental removal of jQuery dependencies
- Test all interactive components thoroughly
- Update JavaScript to Bootstrap 5 vanilla JS API

#### 4. Browser Compatibility
**Risk:** Issues in older browsers
**Impact:** MEDIUM - Reduced accessibility
**Probability:** LOW
**Mitigation:**
- Test in all major browsers
- Provide polyfills if needed
- Document browser support requirements
- Bootstrap 5 has good browser support

#### 5. Performance Degradation
**Risk:** Site becomes slower
**Impact:** MEDIUM - Poor user experience
**Probability:** LOW
**Mitigation:**
- Performance testing with Lighthouse
- Bundle size analysis
- Optimize builds (PurgeCSS, minification)
- Bootstrap 5 is actually smaller than Bootstrap 4

#### 6. Accessibility Regression
**Risk:** WCAG compliance issues
**Impact:** HIGH - Legal/compliance risk
**Probability:** MEDIUM
**Mitigation:**
- Automated accessibility testing (Pa11y, axe)
- Manual screen reader testing
- Keyboard navigation testing
- Bootstrap 5 has better accessibility than Bootstrap 4

---

## Resource Requirements

**Skills Needed:**
- Bootstrap 4 & 5 expertise
- SCSS/Sass
- JavaScript (ES6+)
- Vue.js 2 (and optionally Vue 3)
- Webpack
- Drupal theming
- Accessibility (WCAG 2.2)
- Testing (BackstopJS, Pa11y, Behat)

**Tools & Software:**
- Node.js 16+ (for building)
- npm/yarn
- Git
- Docksal (or DDEV)
- BackstopJS
- Pa11y
- Lighthouse
- Browser testing tools

---

## Success Metrics

### Technical Metrics
- [ ] **All ~70 modules migrated** to Bootstrap 5.3+
- [ ] **Zero Bootstrap jQuery dependencies** (Drupal jQuery retained)
- [ ] **Lighthouse scores 90+** (Performance, Accessibility, Best Practices, SEO)
- [ ] **Bundle size reduced 15-20%** (Bootstrap 5 is smaller)
- [ ] **WCAG 2.2 AA compliance** maintained/improved
- [ ] **Zero visual regressions** in automated tests
- [ ] **All browsers supported** (Chrome, Firefox, Safari, Edge)

### Quality Metrics
- [ ] **100% templates use Bootstrap 5 syntax** (data-bs-*)
- [ ] **All interactive components functional** (modals, dropdowns, etc.)
- [ ] **Cross-browser compatibility verified**
- [ ] **Mobile responsive** at all breakpoints (including new xxl)
- [ ] **Documentation complete and accurate**
- [ ] **Automated tests passing** (visual, accessibility, functional)

### Business Metrics
- [ ] **Zero site downtime** during migration
- [ ] **User feedback positive** (survey)
- [ ] **Performance improvements** measured (Core Web Vitals)
- [ ] **Accessibility scores improved** (Pa11y)
- [ ] **Developer experience improved** (survey)
- [ ] **Migration completed on time and on budget**

### Adoption Metrics
- [ ] **Partner sites upgraded** (track adoption rate)
- [ ] **Support tickets minimal** (< 10 migration-related issues)
- [ ] **Community contribution** (code, testing, documentation)

---

## Next Steps (Immediate Actions)

### Week 1-2: Quick Wins

#### 1. Study Reference Implementation ✅
- [x] Analyze lb_accordion module
- [ ] Document migration approach
- [ ] Extract reusable patterns
- [ ] Create migration template/checklist

#### 2. Create Migration Infrastructure
```bash
# Create migration branch
git checkout -b feature/bootstrap-5-migration

# Create documentation structure
mkdir -p docs/bootstrap-5-migration/{decisions,sprints,testing,reference}

# This file you're reading now
docs/bootstrap-5-migration/MIGRATION_STRATEGY.md

# Decision tracking
docs/bootstrap-5-migration/decisions/QUESTIONNAIRE.md
```

#### 3. Set Up Automated Testing
```bash
# Install testing tools
npm install -D backstopjs pa11y-ci lighthouse

# Configure BackstopJS
cd docs/bootstrap-5-migration/testing
backstop init
# Edit backstop.json with scenarios

# Configure Pa11y
cat > .pa11yci.json <<EOF
{
  "defaults": {
    "standard": "WCAG2AA",
    "runners": ["axe", "htmlcs"]
  },
  "urls": [
    "http://yusaopeny.docksal.site/",
    "http://yusaopeny.docksal.site/branch/example",
    "http://yusaopeny.docksal.site/program/example"
  ]
}
EOF
```

#### 4. Answer Decision Questionnaire
- See `docs/bootstrap-5-migration/decisions/QUESTIONNAIRE.md`
- Answer 10 key questions
- Document decisions
- Get stakeholder approval

#### 5. Create Proof of Concept
```bash
# Pick a simple module to test approach
cd docroot/modules/contrib/lb_statistics

# Follow lb_accordion pattern
# Update to Bootstrap 5
# Document process
# Verify approach works
```

### Week 3-4: Foundation

#### 6. Start Core Theme Migration
```bash
cd docroot/themes/contrib/openy_carnation

# Update dependencies
npm install bootstrap@^5.3.3 @popperjs/core@^2.11.8 --save

# Update build tools
npm install webpack@^5.91.0 --save-dev

# Build and test locally
npm run build

# Test in Docksal
fin drush cr
# Manual testing
```

#### 7. Activity Finder Research & Isolation
```bash
cd docroot/modules/contrib/yusaopeny_activity_finder

# Audit BootstrapVue usage
grep -r "b-" openy_af4_vue_app/src/ > bootstrap-vue-audit.txt

# Implement temporary isolation (Phase 4A)
# Create scoped Bootstrap 4 CSS
# Test Activity Finder still works
# Test with theme on Bootstrap 5
```

### Decision Points

**By Week 2:**
- [ ] Confirm Activity Finder migration approach (Option B or C)
- [ ] Get stakeholder approval for timeline
- [ ] Assign resources to project

**By Week 4:**
- [ ] Validate openy_carnation migration feasibility
- [ ] Confirm parallel work plan for Phases 3-5
- [ ] Finalize sprint plan

**By Week 8:**
- [ ] Go/No-go decision for full migration
- [ ] Review progress vs. timeline
- [ ] Adjust resources if needed

---

## Additional Resources

### Official Documentation
- [Bootstrap 5 Migration Guide](https://getbootstrap.com/docs/5.3/migration/)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3/)
- [Bootstrap 5 Examples](https://getbootstrap.com/docs/5.3/examples/)
- [BootstrapVueNext (Alpha)](https://github.com/bootstrap-vue-next/bootstrap-vue-next)
- [Bootstrap 5 Blog](https://blog.getbootstrap.com/)

### Migration Tools
- [Bootstrap 5 Migrate Tool (CLI)](https://github.com/coliff/bootstrap-5-migrate-tool)
- [Bootstrap 5 Upgrade Tool](https://bootstrap.build/app/upgrade)
- [Bootstrap 4 to 5 Diff](https://bootstrapdiff.com/compare-versions)

### Testing Tools
- [BackstopJS (Visual Regression)](https://github.com/garris/BackstopJS)
- [Pa11y (Accessibility)](https://pa11y.org/)
- [Lighthouse (Performance)](https://developers.google.com/web/tools/lighthouse)
- [axe DevTools](https://www.deque.com/axe/devtools/)
- [WAVE Browser Extension](https://wave.webaim.org/extension/)

### Community Resources
- [Bootstrap 5 Discussions](https://github.com/twbs/bootstrap/discussions)
- [Bootstrap 5 Stack Overflow](https://stackoverflow.com/questions/tagged/bootstrap-5)
- [Y USA Open YMCA Docs](https://ds-docs.y.org)
- [Y USA GitHub](https://github.com/YCloudYUSA)
- [Drupal Bootstrap Theme](https://www.drupal.org/project/bootstrap)

### Learning Resources
- [Bootstrap 5 Crash Course](https://www.youtube.com/results?search_query=bootstrap+5+crash+course)
- [Bootstrap 4 to 5 Migration Guide (Video)](https://www.youtube.com/results?search_query=bootstrap+4+to+5+migration)
- [Vue.js 3 Migration Guide](https://v3-migration.vuejs.org/)

---

## Appendix

### A. Module Inventory

See: `docs/bootstrap-5-migration/reference/MODULE_INVENTORY.xlsx`

**Summary:**
- 1 theme: openy_carnation (BS 4.4.1)
- 3 Activity Finder apps: (BS 4.6.1 + BootstrapVue 2.22.0)
- 20 lb_* modules (1 on BS 5.3.3, 19 on BS 4.4.1)
- 16 ws_small_y modules (BS 4.4.1)
- 10+ y_* modules (BS 4.4.1)
- 10+ other modules (BS 4.4.1)
- **Total: ~70 components**

### B. Breaking Changes Cheat Sheet

See: `docs/bootstrap-5-migration/reference/BREAKING_CHANGES_CHEAT_SHEET.md`

**Quick Reference:**
- `data-toggle` → `data-bs-toggle`
- `.btn-block` → `.d-grid`
- `.form-group` → `.mb-3`
- `.custom-select` → `.form-select`
- `.close` → `.btn-close`
- `.ml-*` → `.ms-*`
- `.mr-*` → `.me-*`
- `.text-left` → `.text-start`
- `.text-right` → `.text-end`

### C. Sprint Plan Template

See: `docs/bootstrap-5-migration/sprints/SPRINT_TEMPLATE.md`

**Sprint Structure:**
- Sprint Goals
- User Stories
- Tasks
- Acceptance Criteria
- Testing Checklist
- Retrospective

### D. Testing Checklist

See: `docs/bootstrap-5-migration/testing/TESTING_CHECKLIST.md`

**Categories:**
- Visual Testing
- Functional Testing
- Accessibility Testing
- Performance Testing
- Browser Testing
- Mobile Testing

---

## Document Control

**Version History:**

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-10-08 | - | Initial comprehensive strategy document |
| 1.1 | 19 December 2025 | - | Added GitHub Advanced Markdown formatting |

**Review Schedule:**
- Weekly during active migration
- Monthly after completion
- Update as needed based on findings

**Approval:**
- [ ] Technical Lead
- [ ] Project Manager
- [ ] Product Owner

> [!NOTE]
> **Questions?**
> - GitHub Issues: https://github.com/YCloudYUSA/yusaopeny-project/issues
> - GitHub Discussions: https://github.com/YCloudYUSA/yusaopeny/discussions
> - Documentation: https://ds-docs.y.org

---

**End of Migration Strategy Document**
