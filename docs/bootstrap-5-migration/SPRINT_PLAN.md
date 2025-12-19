# Bootstrap 5 Migration - Sprint Plan

| | |
|---|---|
| **Project** | Y USA Open YMCA Bootstrap 4 → 5 Migration |
| **Timeline** | 9-12 months (Gradual approach) |
| **Start Date** | TBD |
| **Sprint Duration** | 2 weeks |
| **Total Sprints** | 20-24 sprints |
| **Last Updated** | 19 December 2025 |

---

## Executive Summary

Based on questionnaire responses, this is a **gradual, low-risk, community-focused migration** with the following characteristics:

> [!TIP]
> **Key Parameters:**
> - **Timeline:** 9-12 months, sequential phases
> - **Resources:** 1 full-time developer + contractor support (as needed)
> - **Testing:** Progressive (Basic → Standard → Comprehensive)
> - **Rollout:** Multi-tier (New sites → Safe sites → Flagged → Staged distribution)
> - **Priority Order:** Theme → WS modules → LB modules → Content Types → Activity Finder
> - **Success Criteria:** Technical correctness first, avoid community resistance
> - **Maintenance:** Hybrid (core team + community), exploring Vite+TypeScript future

---

## Sprint Structure Overview

### Phase 1: Preparation & Foundation (Sprints 1-3, 6 weeks)
- Set up infrastructure
- Study reference implementations
- Migrate core theme
- Activity Finder isolation

### Phase 2: Website Services Modules (Sprints 4-7, 8 weeks)
- ws_small_y suite (15 modules)
- ws_event, ws_promotion, ws_colorway_canada
- Other WS modules

### Phase 3: Layout Builder Modules (Sprints 8-13, 12 weeks)
- High priority LB modules (hero, cards, carousel, modal, webform)
- Medium priority LB modules
- Lower priority LB modules
- ~20 modules total

### Phase 4: Content Type Modules (Sprints 14-17, 8 weeks)
- y_branch, y_program, y_camp, y_facility
- y_lb, y_donate, y_branch_menu
- All critical content types

### Phase 5: Activity Finder Migration (Sprints 18-21, 8 weeks)
- Remove BootstrapVue dependencies
- Migrate to Bootstrap 5 vanilla JS
- Comprehensive testing

### Phase 6: Testing, Documentation & Rollout (Sprints 22-24, 6 weeks)
- Comprehensive testing
- Documentation updates
- Staged rollout to distribution

---

## Detailed Sprint Breakdown

---

## 🏁 PHASE 1: PREPARATION & FOUNDATION

### Sprint 1: Infrastructure & Research (Weeks 1-2)

**Sprint Goal:** Set up migration infrastructure and create baseline

**Resources:** 1 Developer

**Tasks:**
1. **Migration Branch & Documentation**
   - Create `feature/bootstrap-5-migration` branch
   - Set up documentation structure (already done ✅)
   - Create migration log template

2. **Study Reference Implementation**
   - Analyze lb_accordion module (Bootstrap 5.3.3)
   - Document migration patterns
   - Extract reusable scripts/approaches
   - Create migration checklist template

3. **Testing Infrastructure**
   - Install BackstopJS for visual regression (Phase 2+)
   - Install Pa11y for accessibility testing (Phase 2+)
   - Configure basic testing scenarios
   - Create baseline screenshots of current Bootstrap 4 site

4. **Date Picker Investigation** 🔍
   - Test Flatpickr with openy_repeat module
   - Test Tempus Dominus integration
   - Test native HTML5 date input
   - Document pros/cons of each
   - **Decision:** Choose date picker replacement

5. **Module Inventory**
   - Audit which modules are actually used/enabled
   - Document dependencies between modules
   - Identify any unused modules that can be skipped

**Acceptance Criteria:**
- [ ] Migration branch created and infrastructure ready
- [ ] lb_accordion patterns documented
- [ ] Testing tools installed and configured
- [ ] Date picker decision made and documented
- [ ] Module inventory complete

**Testing:** Manual only (infrastructure sprint)

**Deliverables:**
- Migration infrastructure ready
- Date picker decision documented
- Module inventory spreadsheet
- Migration checklist template

---

### Sprint 2: Core Theme Migration - Part 1 (Weeks 3-4)

**Sprint Goal:** Begin openy_carnation theme migration

**Resources:** 1 Developer

**Tasks:**
1. **Update Dependencies**
   ```bash
   cd docroot/themes/contrib/openy_carnation
   npm install bootstrap@^5.3.3 @popperjs/core@^2.11.8 --save
   npm install webpack@^5.91.0 webpack-cli@^5.1.4 --save-dev
   npm install sass@^1.75.0 sass-loader@^14.2.0 --save-dev
   ```

2. **Update SCSS Files (Part 1 - Core)**
   - Update `src/scss/_init.scss` (Bootstrap variable overrides)
   - Update `src/scss/_variables.scss` (theme variables)
   - Update `src/scss/_overrides.scss` (component overrides)
   - Update `src/scss/style.scss` (main entry point)
   - Test compilation

3. **Update Build System**
   - Update `webpack.build.js` to Webpack 5
   - Update `webpack.dev.js` for development
   - Update `package.json` scripts
   - Test build process

4. **Initial Build & Test**
   - Build theme: `npm run build`
   - Deploy to local Docksal environment
   - Visual inspection of homepage
   - Document issues found

**Acceptance Criteria:**
- [ ] Dependencies updated to Bootstrap 5.3.3
- [ ] Core SCSS files updated
- [ ] Theme builds without errors
- [ ] Homepage renders without major breaks

**Testing:** Basic manual (visual inspection)

**Known Issues to Document:**
- List any breaking changes discovered
- Note components that need attention in Sprint 3

---

### Sprint 3: Core Theme Migration - Part 2 + Activity Finder Isolation (Weeks 5-6)

**Sprint Goal:** Complete theme migration and isolate Activity Finder

**Resources:** 1 Developer

**Tasks:**

**Theme Completion:**
1. **Update Component SCSS Files (~60 files)**
   - Update `src/scss/component/*.scss` files
   - Update `src/scss/modules/*.scss` files
   - Update `src/scss/paragraphs/*.scss` files
   - Replace deprecated classes:
     - `.ml-*` → `.ms-*`
     - `.mr-*` → `.me-*`
     - `.text-left` → `.text-start`
     - `.text-right` → `.text-end`
     - `.close` → `.btn-close`

2. **Update Templates (Twig Files)**
   - Automated find/replace for data attributes:
     - `data-toggle` → `data-bs-toggle`
     - `data-target` → `data-bs-target`
     - `data-dismiss` → `data-bs-dismiss`
   - Manual updates for structural changes:
     - `.btn-block` → `.d-grid` wrapper
     - `.form-group` → margin utilities
     - `.custom-select` → `.form-select`

3. **Update JavaScript**
   - Update Bootstrap component initialization
   - Convert jQuery-based Bootstrap to vanilla JS where feasible
   - Test interactive components (modals, dropdowns, tooltips)

4. **Update Libraries YAML**
   - Update `openy_carnation.libraries.yml`
   - Ensure Bootstrap 5 bundle is loaded
   - Increment version numbers

**Activity Finder Isolation:**
5. **Scope Bootstrap 4 CSS to Activity Finder**
   ```scss
   // openy_af4_vue_app/scss/activity-finder-scoped.scss
   .activity-finder-wrapper {
     @import "bootstrap-4-vendored";
     // All Activity Finder styles scoped here
   }
   ```

6. **Update Activity Finder Templates**
   - Wrap Activity Finder blocks in `.activity-finder-wrapper`
   - Test that Activity Finder still works with scoped Bootstrap 4
   - Test that theme uses Bootstrap 5 without conflicts

7. **Test Theme + Activity Finder Coexistence**
   - Test page with both theme (BS5) and Activity Finder (BS4)
   - Verify no CSS conflicts
   - Document any issues

**Acceptance Criteria:**
- [ ] All theme SCSS files updated to Bootstrap 5
- [ ] All templates use Bootstrap 5 syntax (data-bs-*)
- [ ] Interactive components functional (modals, dropdowns, etc.)
- [ ] Activity Finder isolated with scoped Bootstrap 4 CSS
- [ ] Theme and Activity Finder work together without conflicts
- [ ] Build completes successfully

**Testing:** Basic manual testing
- Test all content types (Branch, Program, Class, Event, News, Blog, Landing)
- Test navigation (desktop & mobile)
- Test modals, dropdowns, alerts
- Test Activity Finder still works

**Deliverables:**
- openy_carnation theme migrated to Bootstrap 5.3.3
- Activity Finder isolated on Bootstrap 4
- Migration log with issues and solutions
- Theme migration checklist completed

> [!IMPORTANT]
> **Decision Point:**
> - **GO/NO-GO for Phase 2:** Is theme stable enough to proceed with modules?

---

## 🔧 PHASE 2: WEBSITE SERVICES MODULES

### Sprint 4: ws_small_y Suite - Part 1 (Weeks 7-8)

**Sprint Goal:** Migrate first batch of ws_small_y modules

**Resources:** 1 Developer + Consider bringing in Contractor for parallel work

**Modules (5-6 modules):**
1. small_y_hero (high priority)
2. small_y_cards (high priority)
3. small_y_carousels (high priority)
4. small_y_accordions
5. small_y_alerts
6. small_y_tabs

**Standard Migration Process (per module):**
1. Update `package.json` dependencies
   ```bash
   npm install bootstrap@^5.3.3 @popperjs/core@^2.11.8 --save
   ```
2. Update webpack config (follow lb_accordion pattern)
3. Update SCSS files (Bootstrap 5 syntax)
4. Update templates (data-bs-* attributes)
5. Build: `npm run build`
6. Test module functionality

**Acceptance Criteria (per module):**
- [ ] Dependencies updated to Bootstrap 5.3.3
- [ ] SCSS uses Bootstrap 5 syntax
- [ ] Templates use data-bs-* attributes
- [ ] Module builds without errors
- [ ] Visual appearance matches Bootstrap 4 version
- [ ] Interactive features work

**Testing:** Basic manual per module

**Contractor Support:**
- If contractor available, assign 2-3 modules to contractor
- Developer takes 3-4 modules
- Parallel work to speed up

**Deliverables:**
- 5-6 ws_small_y modules migrated
- Migration log updated

---

### Sprint 5: ws_small_y Suite - Part 2 (Weeks 9-10)

**Sprint Goal:** Complete remaining ws_small_y modules

**Resources:** 1 Developer + Contractor (if available)

**Modules (9 remaining):**
7. small_y_articles
8. small_y_branch
9. small_y_editor
10. small_y_events
11. small_y_icon_grid
12. small_y_ping_pongs
13. small_y_search
14. ws_small_y_staff
15. ws_small_y_statistics
16. ws_small_y_testimonials

**Process:** Same standard migration process as Sprint 4

**Acceptance Criteria:**
- [ ] All 16 ws_small_y modules migrated to Bootstrap 5.3.3
- [ ] All modules build and function correctly
- [ ] Visual regression testing passed (if enabled)

**Testing:**
- Basic manual testing per module
- Quick visual check across all ws_small_y modules

**Deliverables:**
- ws_small_y suite complete (16 modules)
- Migration patterns documented

---

### Sprint 6: Other Website Services Modules (Weeks 11-12)

**Sprint Goal:** Migrate remaining WS modules

**Resources:** 1 Developer

**Modules:**
1. **ws_event** (event management)
   - Update event templates
   - Test event display
   - Test add-to-calendar functionality

2. **ws_promotion** + submodules
   - ws_promotion (main)
   - ws_promotion_modal
   - ws_promotion_activity_finder
   - Test promotional banners/modals

3. **ws_colorway_canada** + lb_hero_canada
   - Update Canadian theme variant
   - Test hero component

4. **ws_lb_tabs**
   - Update tab component
   - Test tab switching
   - Test keyboard navigation

5. **ws_home_branch** (openy_custom/openy_home_branch/ws_home_branch)
   - Update home branch templates
   - Test location selector

**Acceptance Criteria:**
- [ ] All WS modules migrated to Bootstrap 5.3.3
- [ ] Event functionality works
- [ ] Promotional modals work
- [ ] Canadian theme variant works
- [ ] Tabs functional with keyboard navigation
- [ ] Home branch selector works

**Testing:** Basic manual testing

**Deliverables:**
- All Website Services modules migrated
- Phase 2 complete

> [!NOTE]
> **Decision Point:**
> Consider moving from Basic to Standard testing (add BackstopJS visual regression for Phase 3)

---

### Sprint 7: Testing & Documentation Catch-Up (Weeks 13-14)

**Sprint Goal:** Test all migrated modules together, document patterns, catch up on any issues

**Resources:** 1 Developer

**Tasks:**
1. **Integration Testing**
   - Test theme + all WS modules together
   - Test on sample pages with multiple modules
   - Verify no CSS conflicts between modules
   - Test responsive behavior at all breakpoints

2. **Set Up Standard Testing** (C→A upgrade)
   - Configure BackstopJS scenarios for key pages
   - Configure Pa11y for accessibility testing
   - Create baseline for Layout Builder phase
   - Document testing process

3. **Documentation Updates**
   - Update migration log with lessons learned
   - Document common migration patterns
   - Create troubleshooting guide
   - Update module READMEs

4. **Fix Any Issues**
   - Address bugs found in integration testing
   - Fix visual regressions
   - Resolve conflicts

5. **Community Preview**
   - Deploy Bootstrap 5 to internal test site
   - Share with core contributors for feedback
   - Gather initial feedback (validate no community resistance)

**Acceptance Criteria:**
- [ ] All Phase 1-2 modules tested together
- [ ] Standard testing infrastructure ready (BackstopJS + Pa11y)
- [ ] Documentation updated
- [ ] All critical bugs fixed
- [ ] Initial community feedback positive

**Testing:** Standard (visual regression + accessibility)

**Deliverables:**
- Phase 1-2 testing complete
- Standard testing infrastructure ready
- Community feedback report

---

## 🎨 PHASE 3: LAYOUT BUILDER MODULES

### Sprint 8: High Priority LB Modules - Part 1 (Weeks 15-16)

**Sprint Goal:** Migrate critical Layout Builder modules

**Resources:** 1 Developer + Contractor (recommended for this phase)

**Modules (Batch 1):**
1. **lb_hero** - Homepage hero component (CRITICAL)
2. **lb_cards** - Card layouts (CRITICAL, commonly used)
3. **lb_carousel** - Image carousels (CRITICAL, visual)

**Migration Process:**
- Follow lb_accordion reference implementation
- Standard migration process (dependencies, SCSS, templates, build)
- Extra attention to JavaScript for interactive components

**Special Considerations:**

**lb_hero:**
- High visibility component (homepage)
- Test responsive hero images
- Test call-to-action buttons

**lb_cards:**
- Test card grid layouts
- Test card variations (image, no image, etc.)
- Verify card deck replacement (removed in BS5)

**lb_carousel:**
- **Critical:** Bootstrap 5 carousel JavaScript API changed
- Test touch swipe on mobile
- Test keyboard controls (arrow keys)
- Test auto-play behavior
- Test carousel indicators and controls

**Acceptance Criteria:**
- [ ] All 3 modules migrated to Bootstrap 5.3.3
- [ ] Visual appearance matches Bootstrap 4
- [ ] Interactive features work (carousel navigation)
- [ ] Responsive at all breakpoints
- [ ] Accessibility maintained (keyboard navigation)

**Testing:** Standard (visual regression + accessibility)

**Deliverables:**
- 3 high-priority LB modules migrated

---

### Sprint 9: High Priority LB Modules - Part 2 (Weeks 17-18)

**Sprint Goal:** Complete high-priority LB modules

**Resources:** 1 Developer + Contractor

**Modules (Batch 2):**
4. **lb_modal** - Modal dialogs (CRITICAL, complex)
5. **lb_webform** - Form integration (CRITICAL)

**Special Considerations:**

**lb_modal:**
- **CRITICAL:** Bootstrap 5 modal API changed significantly
- Test modal open/close
- Test backdrop behavior
- Test keyboard navigation (ESC key closes modal)
- Test focus trapping
- Test multiple modals (if used)
- JavaScript API change:
  ```javascript
  // Bootstrap 4
  $('#myModal').modal('show');

  // Bootstrap 5
  const modal = new bootstrap.Modal(document.getElementById('myModal'));
  modal.show();
  ```

**lb_webform:**
- Coordinate with webform_bootstrap module
- Test all form element types (text, select, checkbox, radio, file, etc.)
- Test form validation
- Test form submission (AJAX)
- Test error states
- **Form class changes critical:**
  - `.form-group` → `.mb-3`
  - `.custom-select` → `.form-select`
  - `.custom-checkbox` → `.form-check`
  - `.custom-radio` → `.form-check`
  - `.input-group-prepend/append` → simplified `.input-group`

**Acceptance Criteria:**
- [ ] lb_modal migrated with fully functional modals
- [ ] Modal keyboard navigation works (ESC, focus trap)
- [ ] lb_webform migrated with all form elements working
- [ ] Form validation works
- [ ] Form submission works (including AJAX)
- [ ] Accessibility maintained (WCAG 2.2 AA)

**Testing:** Standard + extra manual testing for complex interactions

**Deliverables:**
- lb_modal and lb_webform migrated (5 high-priority modules complete)

---

### Sprint 10: Medium Priority LB Modules - Part 1 (Weeks 19-20)

**Sprint Goal:** Migrate medium-priority LB modules

**Resources:** 1 Developer + Contractor

**Modules (Batch 3):**
6. lb_branch_amenities_blocks
7. lb_branch_hours_blocks
8. lb_branch_social_links_blocks
9. lb_ping_pong (alternating content blocks)
10. lb_promo (promotional blocks)

**Process:** Standard migration process

**These are branch-related blocks, so coordination:**
- Test together on branch pages
- Ensure consistent styling
- Test with y_branch content type

**Acceptance Criteria:**
- [ ] All 5 modules migrated to Bootstrap 5.3.3
- [ ] Branch-related blocks work together on branch pages
- [ ] Visual consistency maintained

**Testing:** Standard

**Deliverables:**
- 5 medium-priority LB modules migrated (10 total)

---

### Sprint 11: Medium Priority LB Modules - Part 2 (Weeks 21-22)

**Sprint Goal:** Continue medium-priority LB modules

**Resources:** 1 Developer + Contractor

**Modules (Batch 4):**
11. lb_grid_cta + lb_grid_icon (submodule)
12. lb_partners_blocks
13. lb_related_articles_blocks
14. lb_related_events_blocks
15. lb_simple_menu

**Process:** Standard migration process

**Note on lb_grid_cta:**
- Has submodule lb_grid_icon
- Test both together
- Test grid layouts at all breakpoints

**Acceptance Criteria:**
- [ ] All 5 modules (+1 submodule) migrated
- [ ] Grid layouts responsive
- [ ] Related content blocks functional

**Testing:** Standard

**Deliverables:**
- 5 more LB modules migrated (15 total)

---

### Sprint 12: Lower Priority LB Modules (Weeks 23-24)

**Sprint Goal:** Complete remaining LB modules

**Resources:** 1 Developer

**Modules (Batch 5):**
16. lb_staff_members_blocks
17. lb_statistics
18. lb_table
19. lb_testimonial_blocks
20. lb_activity_finder (if applicable - may skip if not used)

**Special Consideration - lb_table:**
- **Bootstrap 5 change:** Nested tables no longer inherit styles
- Test nested tables if used
- Test table variants (`.table-striped`, `.table-hover`)
- Test responsive tables

**Acceptance Criteria:**
- [ ] All remaining LB modules migrated
- [ ] Tables render correctly (including nested)
- [ ] All 20 lb_* modules complete

**Testing:** Standard

**Deliverables:**
- All Layout Builder modules migrated (~20 modules)
- Phase 3 complete

---

### Sprint 13: LB Integration Testing & Date Picker (Weeks 25-26)

**Sprint Goal:** Test all LB modules together, implement date picker replacement

**Resources:** 1 Developer

**Tasks:**

**LB Integration Testing:**
1. Create test pages with multiple LB components
2. Test all 20 LB modules together
3. Run visual regression tests (BackstopJS)
4. Run accessibility tests (Pa11y)
5. Test responsive at all breakpoints (sm, md, lg, xl, **xxl** new!)
6. Fix any issues found

**Date Picker Replacement:**
2. Implement chosen date picker (from Sprint 1 investigation)
3. Update openy_repeat module
4. Replace bootstrap-datepicker 1.3.0
5. Test schedule dashboard
6. Test date selection functionality

**Other Supporting Modules:**
7. Update **openy_node_alert** (alert display)
8. Update **openy_map/openy_map_lb** (map display)
9. Update **openy_custom/openy_calc** (calculator)

**Acceptance Criteria:**
- [ ] All LB modules tested together
- [ ] Visual regression tests passed
- [ ] Accessibility tests passed (WCAG 2.2 AA)
- [ ] Date picker replaced and functional
- [ ] Supporting modules migrated

**Testing:** Standard (visual regression + accessibility)

**Deliverables:**
- Phase 3 complete with all tests passing
- Date picker replaced
- Supporting modules migrated

---

## 📄 PHASE 4: CONTENT TYPE MODULES

### Sprint 14: Y Branch & Related Modules (Weeks 27-28)

**Sprint Goal:** Migrate branch-related content type modules

**Resources:** 1 Developer

**Modules:**
1. **y_branch** (Branch content type)
   - Update branch page templates
   - Test branch display
   - Test amenities integration (from lb_branch_amenities_blocks)
   - Test hours integration (from lb_branch_hours_blocks)
   - Test social links integration (from lb_branch_social_links_blocks)
   - Test location map integration

2. **y_branch_menu** (Branch menu)
   - Update branch menu component
   - Test menu display on branch pages

**Since branch-related LB blocks were already migrated in Sprint 10, focus on:**
- Template updates for y_branch content type
- Integration with migrated LB blocks
- Overall branch page experience

**Acceptance Criteria:**
- [ ] y_branch content type migrated
- [ ] y_branch_menu migrated
- [ ] Branch pages render correctly
- [ ] All branch page components work together
- [ ] Responsive branch pages

**Testing:** Standard

**Deliverables:**
- Branch-related modules migrated
- Branch pages fully functional

---

### Sprint 15: Y Program & Camp Modules (Weeks 29-30)

**Sprint Goal:** Migrate program and camp content types

**Resources:** 1 Developer

**Modules:**
1. **y_program** (Program content type)
   - Update program page templates
   - Test program display
   - Test integration with Activity Finder (still on Bootstrap 4, should work)

2. **y_program_subcategory** (Program subcategories)
   - Update subcategory templates
   - Test subcategory display

3. **y_camp** (Camp content type)
   - Update camp page templates
   - Test camp quick links
   - Test camp menu
   - Test integration with Camp Finder (still on Bootstrap 4, should work)

> [!NOTE]
> Activity Finder and Camp Finder are still isolated on Bootstrap 4
- Test that program/camp pages work with isolated finders
- No conflicts between Bootstrap 5 pages and Bootstrap 4 finders

**Acceptance Criteria:**
- [ ] y_program migrated
- [ ] y_program_subcategory migrated
- [ ] y_camp migrated
- [ ] Program pages functional
- [ ] Camp pages functional
- [ ] No conflicts with Activity/Camp Finders (Bootstrap 4)

**Testing:** Standard

**Deliverables:**
- Program and camp modules migrated

---

### Sprint 16: Y Facility & LB Integration (Weeks 31-32)

**Sprint Goal:** Migrate facility and Layout Builder integration modules

**Resources:** 1 Developer

**Modules:**
1. **y_facility** (Facility content type)
   - Update facility page templates
   - Test facility display

2. **y_lb** (Layout Builder integration)
   - Update LB-specific templates
   - Test Layout Builder integration with content types

3. **y_lb_main_menu_cta_block** (submodule)
   - Update main menu CTA block
   - Test in header/navigation

4. **y_lb_article** (Article LB integration)
   - Update article LB templates
   - Test article display with Layout Builder

**Acceptance Criteria:**
- [ ] y_facility migrated
- [ ] y_lb migrated (including y_lb_main_menu_cta_block)
- [ ] y_lb_article migrated
- [ ] Facility pages functional
- [ ] Layout Builder integration working
- [ ] Main menu CTA functional

**Testing:** Standard

**Deliverables:**
- Facility and LB integration modules migrated

---

### Sprint 17: Y Donate & Content Type Testing (Weeks 33-34)

**Sprint Goal:** Complete remaining content type modules and comprehensive testing

**Resources:** 1 Developer

**Modules:**
1. **y_donate** + lb_donate submodule
   - Update donation templates
   - Test donation display
   - Test lb_donate Layout Builder block

2. **openy_repeat** + lb_repeat_schedules (if not done in Sprint 13)
   - Verify date picker replacement working
   - Update repeat schedules
   - Test schedule dashboard

**Content Type Integration Testing:**
3. **Test All Content Types Together**
   - Create test content for each type
   - Test rendering on different devices
   - Test with various Layout Builder configurations
   - Run visual regression tests
   - Run accessibility tests
   - Fix any issues

4. **Cross-Content Type Testing**
   - Test related content blocks (articles, events)
   - Test menus and navigation
   - Test search functionality
   - Test taxonomy/category displays

**Acceptance Criteria:**
- [ ] y_donate migrated (including lb_donate)
- [ ] All content types tested together
- [ ] Visual regression tests passed
- [ ] Accessibility tests passed
- [ ] No cross-content-type issues

**Testing:** Standard

**Deliverables:**
- All content type modules migrated (~10 modules)
- Phase 4 complete
- Integration testing passed

> [!IMPORTANT]
> **Decision Point:**
> - **GO/NO-GO for Activity Finder Migration:** Ready to tackle Activity Finder? Or delay?
> - If delaying Activity Finder, skip to Phase 6 (Testing & Rollout)

---

## 🎯 PHASE 5: ACTIVITY FINDER MIGRATION

### Sprint 18: Activity Finder Preparation & BootstrapVue Audit (Weeks 35-36)

**Sprint Goal:** Prepare for Activity Finder migration, audit BootstrapVue usage

**Resources:** 1 Developer

**Tasks:**

1. **BootstrapVue Component Audit**
   ```bash
   cd docroot/modules/contrib/yusaopeny_activity_finder

   # Find all BootstrapVue components
   grep -r "b-button\|b-modal\|b-card\|b-form\|b-dropdown" openy_af4_vue_app/src/
   grep -r "b-" openy_af_vue_app/src/
   grep -r "b-" openy_cf_vue_app/src/
   ```

   - Document every BootstrapVue component used
   - Count occurrences
   - Prioritize by usage frequency

2. **Create Bootstrap 5 Component Library**
   - Create reusable Vue components for Bootstrap 5
   - Design components to replace BootstrapVue:
     - Bs5Button (replaces b-button)
     - Bs5Modal (replaces b-modal)
     - Bs5Card (replaces b-card)
     - Bs5FormInput (replaces b-form-input)
     - Bs5FormSelect (replaces b-form-select)
     - Bs5FormCheckbox (replaces b-form-checkbox)
     - Bs5Dropdown (replaces b-dropdown)
     - Others as needed

3. **Test Bootstrap 5 Components**
   - Create test page with new components
   - Verify styling matches BootstrapVue
   - Test interactive behavior
   - Document any differences

4. **Migration Strategy Document**
   - Document component replacement strategy
   - Create migration checklist for each Vue app
   - Plan order: Camp Finder → Activity Finder → Activity Finder 4

**Acceptance Criteria:**
- [ ] BootstrapVue usage fully documented
- [ ] Bootstrap 5 component library created
- [ ] Components tested and working
- [ ] Migration strategy documented

**Testing:** Basic manual (proof of concept)

**Deliverables:**
- BootstrapVue audit report
- Bootstrap 5 component library
- Activity Finder migration plan

---

### Sprint 19: Camp Finder Migration (Weeks 37-38)

**Sprint Goal:** Migrate Camp Finder to Bootstrap 5

**Resources:** 1 Developer + Contractor (Vue.js experience helpful)

**Module:** openy_cf_vue_app (Camp Finder)

**Tasks:**

1. **Update Dependencies**
   ```json
   // openy_cf_vue_app/package.json
   {
     "dependencies": {
       "bootstrap": "^5.3.3",  // was implicit via bootstrap-vue
       "@popperjs/core": "^2.11.8",
       // REMOVE: "bootstrap-vue": "^2.22.0"
     }
   }
   ```

2. **Replace BootstrapVue Components**
   - Replace all `b-*` components with Bootstrap 5 equivalents
   - Use component library from Sprint 18
   - Update component imports
   - Update component props/events

3. **Update Templates**
   - Update data attributes (data-toggle → data-bs-toggle)
   - Update class names where needed
   - Remove BootstrapVue-specific classes

4. **Update Build Process**
   - Import Bootstrap 5 JavaScript
   - Remove BootstrapVue imports
   - Build: `npm run build`

5. **Test Camp Finder**
   - Location selection
   - Date picker (v-calendar compatibility check)
   - Filters (category, age, etc.)
   - Results display
   - Session details
   - Mobile responsive
   - Accessibility

**Acceptance Criteria:**
- [ ] Camp Finder migrated to Bootstrap 5
- [ ] All BootstrapVue dependencies removed
- [ ] Camp Finder fully functional
- [ ] Visual appearance matches previous version
- [ ] Mobile responsive
- [ ] Accessible (WCAG 2.2 AA)

**Testing:** Standard + comprehensive manual testing

**Deliverables:**
- Camp Finder migrated to Bootstrap 5
- Migration lessons learned documented

---

### Sprint 20: Activity Finder Migration (Weeks 39-40)

**Sprint Goal:** Migrate Activity Finder to Bootstrap 5

**Resources:** 1 Developer + Contractor

**Module:** openy_af_vue_app (Activity Finder)

**Tasks:**
- Same process as Sprint 19 (Camp Finder)
- Update dependencies
- Replace BootstrapVue components
- Update templates
- Update build process
- Test thoroughly

**Activity Finder Features to Test:**
- Program search
- Filters (location, category, age, day/time)
- Results grid/list view
- Session details
- Registration links
- Add to calendar
- Mobile responsive
- Accessibility

**Acceptance Criteria:**
- [ ] Activity Finder migrated to Bootstrap 5
- [ ] All BootstrapVue dependencies removed
- [ ] Activity Finder fully functional
- [ ] All filters working
- [ ] Results display working
- [ ] Mobile responsive
- [ ] Accessible

**Testing:** Standard + comprehensive manual testing

**Deliverables:**
- Activity Finder migrated to Bootstrap 5

---

### Sprint 21: Activity Finder 4 Migration (Weeks 41-42)

**Sprint Goal:** Migrate Activity Finder 4 (most modern) to Bootstrap 5

**Resources:** 1 Developer + Contractor

**Module:** openy_af4_vue_app (Activity Finder 4)

**Tasks:**
- Same process as Sprints 19-20
- Update dependencies
- Replace BootstrapVue components
- Update templates
- Update build process
- Test thoroughly

**Activity Finder 4 is the most modern implementation:**
- Modern interface
- Advanced filters
- Better UX
- More features

**Acceptance Criteria:**
- [ ] Activity Finder 4 migrated to Bootstrap 5
- [ ] All BootstrapVue dependencies removed
- [ ] Activity Finder 4 fully functional
- [ ] All features working
- [ ] Mobile responsive
- [ ] Accessible

**Testing:** Comprehensive (C→A→B upgrade - this is the final major component)

**Activity Finder Integration Testing:**
- Test all 3 Activity Finder apps
- Test on pages with theme Bootstrap 5
- Test program/camp pages integration
- Run visual regression tests
- Run accessibility tests
- Performance testing (Lighthouse)

**Acceptance Criteria (Phase 5 Complete):**
- [ ] All 3 Activity Finder apps migrated to Bootstrap 5
- [ ] All BootstrapVue removed from codebase
- [ ] Bootstrap 4 scoped CSS removed
- [ ] Integration tests passed
- [ ] Visual regression tests passed
- [ ] Accessibility tests passed
- [ ] Performance maintained or improved

**Testing:** Comprehensive (visual + accessibility + performance)

**Deliverables:**
- All Activity Finder apps migrated to Bootstrap 5
- Phase 5 complete
- **All ~70 components now on Bootstrap 5!**

---

## 📋 PHASE 6: TESTING, DOCUMENTATION & ROLLOUT

### Sprint 22: Comprehensive Testing & Bug Fixes (Weeks 43-44)

**Sprint Goal:** Final comprehensive testing and bug fixes

**Resources:** 1 Developer + QA Engineer (if available/budget allows)

**This is the C→A→B "Comprehensive Testing" upgrade**

**Tasks:**

1. **Visual Regression Testing (Comprehensive)**
   - Run BackstopJS on all pages/components
   - Test all content types
   - Test all Layout Builder components
   - Test Activity Finder apps
   - Test at all breakpoints (sm, md, lg, xl, **xxl**)
   - Review and approve all visual changes
   - Fix any regressions

2. **Accessibility Testing (Comprehensive)**
   - Run Pa11y on all pages
   - Manual screen reader testing (NVDA, JAWS, VoiceOver)
   - Keyboard navigation testing (no mouse)
   - Color contrast validation
   - Focus indicator testing
   - ARIA labels validation
   - Ensure WCAG 2.2 AA compliance

3. **Performance Testing**
   - Run Lighthouse audits on key pages
   - Target: 90+ in all categories
   - Bundle size comparison (Bootstrap 4 vs 5)
   - Page load times
   - Time to Interactive (TTI)
   - Core Web Vitals (LCP, FID, CLS)

4. **Cross-Browser Testing**
   - Chrome (latest)
   - Firefox (latest)
   - Safari (latest)
   - Edge (latest)
   - Mobile Safari (iOS)
   - Mobile Chrome (Android)

5. **Functional Testing**
   - Test all forms (submission, validation)
   - Test all interactive components (modals, dropdowns, carousels, tabs, accordions)
   - Test Activity Finder (all 3 apps)
   - Test navigation (desktop & mobile)
   - Test search functionality
   - Test user authentication flows

6. **Bug Fixes**
   - Fix all critical bugs
   - Fix high-priority bugs
   - Document medium/low priority bugs for post-launch
   - Regression testing after fixes

**Acceptance Criteria:**
- [ ] Visual regression tests passed (or differences approved)
- [ ] Accessibility tests passed (WCAG 2.2 AA)
- [ ] Performance tests passed (Lighthouse 90+)
- [ ] Cross-browser testing passed
- [ ] All functional tests passed
- [ ] Critical and high-priority bugs fixed

**Testing:** Comprehensive (all methods)

**Deliverables:**
- Comprehensive testing report
- Bug fix log
- Performance comparison report (Bootstrap 4 vs 5)
- Test results documentation

---

### Sprint 23: Documentation & Community Preparation (Weeks 45-46)

**Sprint Goal:** Complete documentation and prepare community for rollout

**Resources:** 1 Developer

**Tasks:**

1. **Documentation Updates**
   - Update README.md files (root + profile + all migrated modules)
   - Update CLAUDE.md with Bootstrap 5 section
   - Create BREAKING_CHANGES.md for site builders
   - Update developer documentation
   - Update site builder documentation

2. **Migration Guides**
   - Create "Bootstrap 5 Migration Guide" for site builders
   - Document breaking changes with examples
   - Create troubleshooting guide
   - Document rollback procedure (just in case)

3. **Video Tutorials** (Optional)
   - Record screencast of Bootstrap 5 features
   - Record migration walkthrough
   - Upload to documentation site

4. **Community Communication**
   - **Announcement #1:** "Bootstrap 5 Migration Complete - Testing Phase"
   - Share timeline for rollout
   - Call for beta testers
   - Explain rollout strategy (New sites → Safe sites → Flagged → Staged)

5. **Beta Testing Preparation**
   - Create beta testing checklist
   - Prepare beta test site
   - Identify beta testing partners (if any)
   - Create feedback collection form

6. **Release Notes**
   - Draft release notes
   - List all breaking changes
   - Document new features (xxl breakpoint, improved accessibility, etc.)
   - List bug fixes
   - Credit contributors

**Acceptance Criteria:**
- [ ] All documentation updated
- [ ] Migration guide complete
- [ ] Community announcement sent
- [ ] Beta testing prepared
- [ ] Release notes drafted

**Testing:** Documentation review

**Deliverables:**
- Complete documentation
- Migration guides
- Release notes draft
- Beta testing plan

---

### Sprint 24: Staged Rollout & Launch (Weeks 47-48)

**Sprint Goal:** Execute multi-tier rollout strategy

**Resources:** 1 Developer + Project Manager (for coordination)

**Your Rollout Strategy:** D → C → B → A

**Week 1 (Week 47): Tier 1-2 Rollout**

**Day 1-2: D - New Sites Only**
- Update installation profile to default to Bootstrap 5
- New site builds use Bootstrap 5 by default
- Existing sites unchanged
- Monitor for any issues with new installations

**Day 3-4: C - Safe Sites Direct**
- Identify 3-5 "safe" sites:
  - Less complex configurations
  - Well-tested content types
  - Technical teams available
  - Willing to be early adopters
- Deploy Bootstrap 5 to these sites
- Monitor closely for issues
- Provide support as needed

**Day 5: Initial Feedback**
- Collect feedback from new sites and safe sites
- Address any immediate issues
- Document learnings

**Week 2 (Week 48): Tier 3-4 Rollout**

**Day 1-3: B - Activity Finder Flag**
- Implement Bootstrap 5 feature flag (leverage existing Bootstrap 3/4 flag pattern)
  ```php
  // In openy_activity_finder or similar
  $config['activity_finder_bootstrap_version'] = 5; // or 4
  ```
- Sites can opt-in to Bootstrap 5 when ready
- Document flag usage
- Communicate flag availability to community

**Day 4-7: A - Distribution Staged Rollout**
- **Internal Testing (Day 4):** Deploy to internal test environments
- **Beta Partner Sites (Day 5):** Deploy to 5-10 beta partner sites (if available)
- **Monitor & Support (Day 6):** Provide dedicated support for beta sites
- **Stable Release (Day 7):**
  - Merge Bootstrap 5 migration branch to main
  - Tag release (e.g., v11.0.0 with Bootstrap 5)
  - Update Composer repository
  - **Announcement #2:** "Bootstrap 5 Now Available"
  - Provide upgrade instructions
  - Open office hours for support

**Ongoing (Post-Sprint 24):**
- Monitor adoption rate
- Provide support for migrating sites
- Address issues as they arise
- Track community feedback
- Celebrate success! 🎉

**Acceptance Criteria:**
- [ ] New sites default to Bootstrap 5
- [ ] Safe sites running Bootstrap 5 successfully
- [ ] Feature flag implemented and documented
- [ ] Stable release published
- [ ] Community announcement sent
- [ ] Upgrade documentation available
- [ ] Support available for community

**Testing:** Production monitoring

**Deliverables:**
- Bootstrap 5 launched across all tiers
- Support documentation live
- Community engaged and supported
- **MIGRATION COMPLETE!**

---

## Summary: 24 Sprints Over 48 Weeks (~11 months)

**Phase Breakdown:**
- **Phase 1:** Sprints 1-3 (6 weeks) - Preparation, Theme, AF Isolation
- **Phase 2:** Sprints 4-7 (8 weeks) - Website Services Modules (~16 modules)
- **Phase 3:** Sprints 8-13 (12 weeks) - Layout Builder Modules (~20 modules)
- **Phase 4:** Sprints 14-17 (8 weeks) - Content Type Modules (~10 modules)
- **Phase 5:** Sprints 18-21 (8 weeks) - Activity Finder Migration (3 apps)
- **Phase 6:** Sprints 22-24 (6 weeks) - Testing, Documentation, Rollout

**Total:** 48 weeks (11-12 months) ✅ Within your 9-12 month target

---

## Resource Allocation Summary

**Primary Developer:** Full-time throughout (48 weeks)

**Contractor Support (Recommended):**
- Sprint 4-5 (WS modules batch processing)
- Sprint 8-11 (LB modules batch processing)
- Sprint 19-21 (Activity Finder Vue.js work)
- **Total:** ~8-10 sprints (~16-20 weeks of contractor support)

**QA Engineer (Optional):**
- Sprint 22 (Comprehensive testing)
- **Total:** ~1 sprint (~2 weeks)

**Community Beta Testers:**
- Sprint 24 (Rollout)

---

## Risk Management

**Key Risks & Mitigation:**

1. **Activity Finder Breaks (HIGH RISK)**
   - **Mitigation:**
     - Isolated on Bootstrap 4 early (Sprint 3)
     - Dedicated 4 sprints for careful migration
     - Component library approach
     - Comprehensive testing

2. **Timeline Slippage (MEDIUM RISK)**
   - **Mitigation:**
     - 2-week sprints allow regular re-planning
     - Buffer built into estimates
     - Can adjust scope if needed (skip unused modules)

> [!CAUTION]
> **3. Community Resistance (HIGH RISK - #1 Failure Condition)**
> - **Mitigation:**
>   - Multi-tier rollout (D→C→B→A)
>   - Extensive documentation
>   - Community-first support approach
>   - Beta testing before full rollout
>   - Feature flag allows opt-in

4. **Visual Regressions (MEDIUM RISK)**
   - **Mitigation:**
     - Progressive testing (C→A→B)
     - Visual regression testing (BackstopJS)
     - Beta testing catches issues early

5. **Developer Fatigue (MEDIUM RISK - 48 weeks is long)**
   - **Mitigation:**
     - Regular sprint retrospectives
     - Celebrate milestone completions
     - Contractor support for batch work
     - Variety in work (different modules)

---

## Success Metrics

**Technical Metrics (Priority #1):**
- [ ] All ~70 modules migrated to Bootstrap 5.3+
- [ ] Zero Bootstrap 4 dependencies (except during Activity Finder isolation period)
- [ ] WCAG 2.2 AA compliance maintained
- [ ] Performance maintained or improved (Lighthouse 90+)

**Operational Metrics (Priority #2):**
- [ ] Migration completed within 9-12 months ✅
- [ ] No site downtime during migration
- [ ] Community adoption of Bootstrap 5 (new sites default to BS5)
- [ ] Minimal support burden (< 10 critical issues)

**Quality Metrics (Priority #3):**
- [ ] All templates use Bootstrap 5 syntax (data-bs-*)
- [ ] All interactive components functional
- [ ] Mobile responsive (including new xxl breakpoint)
- [ ] Cross-browser compatibility verified

**Community Success (Your #1 Failure Condition - AVOID):**
- [ ] **Positive community feedback** ✅
- [ ] No community resistance
- [ ] Beta testers satisfied
- [ ] Smooth rollout
- [ ] Active adoption

---

## Sprint Templates

### Standard Sprint Structure

**Sprint Planning (Day 1):**
- Review previous sprint
- Set sprint goal
- Identify tasks
- Estimate effort
- Assign tasks

**Daily Work (Days 2-9):**
- Development
- Testing
- Documentation
- Code review

**Sprint Review (Day 10 - Last Day):**
- Demo completed work
- Gather feedback
- Update documentation
- Document lessons learned
- Plan next sprint

---

## Communication Plan

**Sprint Cadence:**
- Sprint planning every 2 weeks
- Update migration log every sprint
- Community update every 4 weeks (every 2 sprints)

**Milestone Communications:**
- After Sprint 3: "Theme migrated to Bootstrap 5"
- After Sprint 7: "Phase 2 complete - all WS modules migrated"
- After Sprint 13: "Phase 3 complete - all LB modules migrated"
- After Sprint 17: "Phase 4 complete - all content types migrated"
- After Sprint 21: "Phase 5 complete - Activity Finder migrated"
- After Sprint 24: "**Bootstrap 5 migration complete!** 🎉"

---

## Next Steps

1. **Review this sprint plan** with stakeholders
2. **Get approval** for timeline and resources
3. **Schedule Sprint 1** start date
4. **Assign primary developer**
5. **Identify contractor** for future support
6. **Begin Phase 1!**

---

## Questions Before Starting?

- [ ] Start date confirmed?
- [ ] Primary developer assigned?
- [ ] Contractor identified (for sprints 4-5, 8-11, 19-21)?
- [ ] Budget approved?
- [ ] Stakeholders aligned?
- [ ] Community notified of upcoming migration?

---

> [!NOTE]
> **Document Status:** READY FOR APPROVAL
>
> **Next Action:** Schedule Sprint 1 Planning Meeting

---

**End of Sprint Plan**
