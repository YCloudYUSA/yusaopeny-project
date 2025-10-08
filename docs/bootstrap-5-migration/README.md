# Bootstrap 5 Migration Documentation

This directory contains all documentation related to the Bootstrap 4 to Bootstrap 5 migration for the Y USA Open YMCA project.

## Quick Links

- **[Migration Strategy](MIGRATION_STRATEGY.md)** - Comprehensive migration plan (start here!)
- **[Decision Questionnaire](decisions/QUESTIONNAIRE.md)** - 10 questions to guide sprint planning
- **[Reference: lb_accordion](../modules/contrib/lb_accordion/)** - Already migrated to Bootstrap 5.3.3

## Directory Structure

```
docs/bootstrap-5-migration/
├── README.md (this file)
├── MIGRATION_STRATEGY.md         # Comprehensive strategy document
├── decisions/
│   ├── QUESTIONNAIRE.md           # Decision questionnaire (COMPLETE THIS FIRST!)
│   ├── ACTIVITY_FINDER_DECISION.md (coming soon)
│   └── DATE_PICKER_DECISION.md    (coming soon)
├── sprints/
│   ├── SPRINT_1.md                (coming soon)
│   ├── SPRINT_2.md                (coming soon)
│   └── ...
├── testing/
│   ├── TESTING_MATRIX.xlsx        (coming soon)
│   ├── backstop/                  (BackstopJS config)
│   └── pa11y/                     (Pa11y config)
└── reference/
    ├── MODULE_INVENTORY.xlsx      (coming soon)
    └── BREAKING_CHANGES_CHEAT_SHEET.md (coming soon)
```

## Getting Started

### 1. Read the Strategy Document
Start with [MIGRATION_STRATEGY.md](MIGRATION_STRATEGY.md) to understand:
- Current state analysis (~70 modules using Bootstrap 4)
- Common Bootstrap usage patterns
- Key breaking changes (Bootstrap 4 → 5)
- 8-phase migration plan
- Risk assessment
- Timeline & resources (6-9 months)

### 2. Complete the Questionnaire
Answer the [10 decision questions](decisions/QUESTIONNAIRE.md) to determine:
- Timeline preference (3-4 months aggressive vs 6-9 months standard)
- Activity Finder migration approach (critical decision!)
- Available resources (developers, QA, budget)
- Testing requirements
- Rollout strategy

### 3. Review Reference Implementation
Study `lb_accordion` module (already migrated):
```bash
cd docroot/modules/contrib/lb_accordion
# Review package.json, webpack.config.js, SCSS, templates
```

### 4. Set Up Testing
```bash
# Install testing tools
npm install -D backstopjs pa11y-ci lighthouse

# Configure visual regression testing
cd docs/bootstrap-5-migration/testing
backstop init
```

### 5. Create Migration Branch
```bash
git checkout -b feature/bootstrap-5-migration
```

## Migration Overview

### Phase 1: Preparation (2-3 weeks)
- Study lb_accordion reference
- Set up testing infrastructure
- Complete decision questionnaire
- Create proof of concept

### Phase 2: Core Theme (4-6 weeks)
- Migrate openy_carnation theme
- Update Bootstrap 4.4.1 → 5.3.3
- Update all SCSS, templates, JavaScript
- Extensive testing

### Phase 3: Layout Builder Modules (6-8 weeks)
- Migrate 19 lb_* modules (1 already done)
- Follow lb_accordion pattern
- High priority: lb_hero, lb_cards, lb_carousel, lb_modal, lb_webform

### Phase 4: Activity Finder (8-12 weeks)
- **Critical challenge:** BootstrapVue 2 not compatible with Bootstrap 5
- Recommended: Temporary isolation + incremental migration
- Replace BootstrapVue components with Bootstrap 5 vanilla JS

### Phase 5: WS & Y Modules (6-8 weeks)
- Migrate ws_small_y suite (15 modules)
- Migrate y_branch, y_camp, y_program, etc.
- Batch process similar modules

### Phase 6: Supporting Modules (2-3 weeks)
- bootstrap_layout_builder, bootstrap_styles
- Replace bootstrap-datepicker with modern alternative
- Other utilities

### Phase 7: Testing & QA (4-6 weeks)
- Visual regression (BackstopJS)
- Accessibility (Pa11y, WCAG 2.2 AA)
- Performance (Lighthouse)
- Cross-browser testing
- Manual QA

### Phase 8: Documentation & Rollout (2-3 weeks)
- Update documentation
- Staged rollout (internal → beta → production)
- Communication & support

## Key Decisions Required

### 1. Activity Finder Strategy (MOST CRITICAL)
**Options:**
- **A. Isolation + Migration** (RECOMMENDED) - 10-13 weeks, lower risk
- **B. Direct Migration** - 8-10 weeks, higher risk
- **C. BootstrapVueNext** - 12-16 weeks, highest risk (alpha software)
- **D. Keep Bootstrap 4** - 2-3 weeks, permanent technical debt

**Impact:** This decision affects the entire timeline and approach.

### 2. Timeline
- **Aggressive (3-4 months):** Requires 3-4 developers, parallel work, higher risk
- **Standard (6-9 months):** Requires 2-3 developers, mixed approach, balanced risk
- **Gradual (9-12 months):** Requires 1-2 developers, sequential, lowest risk

### 3. Testing Level
- **Comprehensive:** Visual + Accessibility + Performance + Cross-browser (4-6 weeks)
- **Standard:** Visual + Accessibility (3-4 weeks)
- **Basic:** Manual only (2-3 weeks)

## Components Affected

### Bootstrap 4 Usage by Category

**Theme:**
- openy_carnation (Bootstrap 4.4.1)

**Activity Finders:**
- Activity Finder 4 (Bootstrap 4.6.1 + BootstrapVue 2.22.0)
- Activity Finder (BootstrapVue 2.22.0)
- Camp Finder (BootstrapVue 2.22.0)

**Layout Builder (20 modules):**
- ✅ lb_accordion (Bootstrap 5.3.3) - ALREADY DONE
- ❌ 19 others (Bootstrap 4.4.1)

**Website Services (16+ modules):**
- ws_small_y suite (15 modules)
- ws_event, ws_promotion, ws_colorway_canada, etc.

**Content Types (10+ modules):**
- y_branch, y_camp, y_facility, y_program, etc.

**Other:**
- openy_repeat, openy_node_alert, openy_map, etc.

**Total: ~70 components**

## Breaking Changes Highlights

### Data Attributes (ALL CHANGED)
```html
<!-- Bootstrap 4 -->
<button data-toggle="modal" data-target="#myModal">

<!-- Bootstrap 5 -->
<button data-bs-toggle="modal" data-bs-target="#myModal">
```

### Common Class Changes
- `.btn-block` → `.d-grid` wrapper
- `.form-group` → `.mb-3`
- `.custom-select` → `.form-select`
- `.close` → `.btn-close`
- `.ml-*` → `.ms-*` (margin-start)
- `.mr-*` → `.me-*` (margin-end)
- `.text-left` → `.text-start`
- `.text-right` → `.text-end`

### JavaScript API
```javascript
// Bootstrap 4 (jQuery)
$('#myModal').modal('show');

// Bootstrap 5 (Vanilla JS)
const modal = new bootstrap.Modal(document.getElementById('myModal'));
modal.show();
```

### Removed Components
- `.media` component
- `.jumbotron`
- `.card-deck`
- jQuery dependency

## Resources

### Official Bootstrap
- [Bootstrap 5 Migration Guide](https://getbootstrap.com/docs/5.3/migration/)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3/)

### Testing Tools
- [BackstopJS](https://github.com/garris/BackstopJS) - Visual regression
- [Pa11y](https://pa11y.org/) - Accessibility
- [Lighthouse](https://developers.google.com/web/tools/lighthouse) - Performance

### Migration Tools
- [Bootstrap 5 Migrate Tool](https://github.com/coliff/bootstrap-5-migrate-tool)
- [Bootstrap Diff](https://bootstrapdiff.com/)

### Y USA Resources
- [Y USA Docs](https://sd-docs.y.org)
- [GitHub Repository](https://github.com/YCloudYUSA/yusaopeny-project)

## Success Metrics

**Technical:**
- [ ] All ~70 modules migrated to Bootstrap 5.3+
- [ ] Zero jQuery dependencies (except Drupal core)
- [ ] Lighthouse scores 90+
- [ ] Bundle size reduced 15-20%
- [ ] WCAG 2.2 AA compliance maintained
- [ ] Zero visual regressions

**Quality:**
- [ ] 100% templates use Bootstrap 5 syntax
- [ ] All interactive components functional
- [ ] Cross-browser compatibility verified
- [ ] Mobile responsive (including new xxl breakpoint)

**Business:**
- [ ] Zero site downtime
- [ ] Positive user feedback
- [ ] On-time delivery
- [ ] On-budget delivery

## FAQ

### Q: Can we migrate just part of the site to Bootstrap 5?
**A:** Yes, temporarily. We recommend the "Isolation" approach for Activity Finder, which allows the theme to use Bootstrap 5 while Activity Finder stays on Bootstrap 4 temporarily. However, maintaining two Bootstrap versions long-term is not recommended.

### Q: How risky is this migration?
**A:** Medium risk overall. The theme migration is well-understood with low risk. Activity Finder is the highest-risk component due to BootstrapVue incompatibility. Proper testing and staged rollout mitigate risk.

### Q: What's the minimum viable migration?
**A:** At minimum:
1. Migrate openy_carnation theme (4-6 weeks)
2. Isolate Activity Finder with scoped CSS (2-3 weeks)
3. Basic testing (2 weeks)
Total: ~8-11 weeks minimum

### Q: Can we skip some modules?
**A:** Yes! If you're not using certain modules (e.g., ws_small_y, specific lb_* modules), you can skip them. Review the module inventory and identify which are actually in use.

### Q: What if we find bugs after rollout?
**A:** We recommend a staged rollout (internal → beta → production) to catch issues early. Maintain a rollback plan and provide support during rollout. Most issues should be caught during testing phases.

### Q: How much will this cost?
**A:** Estimated costs:
- **Standard (6-9 months, 2-3 devs):** $150K-$250K
- **Aggressive (3-4 months, 3-4 devs):** $200K-$350K
- **Gradual (9-12 months, 1-2 devs):** $100K-$180K

## Next Steps

1. ✅ Read [MIGRATION_STRATEGY.md](MIGRATION_STRATEGY.md)
2. ⬜ Complete [decisions/QUESTIONNAIRE.md](decisions/QUESTIONNAIRE.md)
3. ⬜ Review with stakeholders
4. ⬜ Get approval for timeline and resources
5. ⬜ Begin Phase 1: Preparation

## Questions?

- **GitHub Issues:** https://github.com/YCloudYUSA/yusaopeny-project/issues
- **GitHub Discussions:** https://github.com/YCloudYUSA/yusaopeny/discussions
- **Documentation:** https://sd-docs.y.org

---

**Last Updated:** 2025-10-08
**Document Version:** 1.0
**Status:** Planning Phase
