# Bootstrap 5 Migration Documentation

> **POC environment for upgrading Y USA Open YMCA from Bootstrap 4 to Bootstrap 5**

[![Bootstrap](https://img.shields.io/badge/Bootstrap-4.4.1→5.3.3-purple)](https://getbootstrap.com/docs/5.3/migration/)
[![Modules](https://img.shields.io/badge/Modules-~70-blue)](#components-affected)
[![Timeline](https://img.shields.io/badge/Timeline-6--9_months-green)](#migration-overview)

---

## Quick Links

| Document | Description |
|----------|-------------|
| **[Migration Strategy](MIGRATION_STRATEGY.md)** | Comprehensive migration plan (start here!) |
| **[Sprint Plan](SPRINT_PLAN.md)** | Detailed sprint breakdown |
| **[Decision Questionnaire](decisions/QUESTIONNAIRE.md)** | 10 questions to guide sprint planning |

> [!TIP]
> Start with the **Migration Strategy** document for a complete overview, then complete the **Decision Questionnaire** to determine your approach.

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

> [!IMPORTANT]
> These decisions must be made before beginning the migration. The Activity Finder decision in particular affects the entire timeline and approach.

### 1. Activity Finder Strategy (MOST CRITICAL)

| Option | Timeline | Risk | Notes |
|--------|----------|------|-------|
| **A. Isolation + Migration** | 10-13 weeks | 🟡 Medium | RECOMMENDED |
| **B. Direct Migration** | 8-10 weeks | 🔴 High | Faster but riskier |
| **C. BootstrapVueNext** | 12-16 weeks | 🔴 Very High | Alpha software |
| **D. Keep Bootstrap 4** | 2-3 weeks | 🟢 Low | Permanent technical debt |

> [!CAUTION]
> BootstrapVue 2.x is **NOT** compatible with Bootstrap 5. This is the most critical challenge in the migration.

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

<details>
<summary><strong>Theme (1)</strong></summary>

| Component | Bootstrap Version | Priority |
|-----------|------------------|----------|
| openy_carnation | 4.4.1 | 🔴 Critical |

</details>

<details>
<summary><strong>Activity Finders (3)</strong> - ⚠️ BootstrapVue dependency</summary>

| Component | Bootstrap | BootstrapVue | Vue |
|-----------|-----------|--------------|-----|
| Activity Finder 4 | 4.6.1 | 2.22.0 | 2.6.14 |
| Activity Finder | - | 2.22.0 | 2.6.10 |
| Camp Finder | - | 2.22.0 | 2.6.10 |

</details>

<details>
<summary><strong>Layout Builder Modules (20)</strong></summary>

| Status | Module | Notes |
|--------|--------|-------|
| ✅ | lb_accordion | **Already on Bootstrap 5.3.3** |
| ❌ | lb_hero | High priority |
| ❌ | lb_cards | High priority |
| ❌ | lb_carousel | High priority |
| ❌ | lb_modal | High priority |
| ❌ | lb_webform | High priority |
| ❌ | lb_ping_pong | Medium priority |
| ❌ | lb_statistics | Medium priority |
| ❌ | 12 others | See Migration Strategy |

</details>

<details>
<summary><strong>Website Services (16+ modules)</strong></summary>

- ws_small_y suite (15 modules)
- ws_event, ws_promotion, ws_colorway_canada, etc.

</details>

<details>
<summary><strong>Content Types & Other (20+ modules)</strong></summary>

- y_branch, y_camp, y_facility, y_program, etc.
- openy_repeat, openy_node_alert, openy_map, etc.

</details>

> [!NOTE]
> **Total: ~70 components** - See [Migration Strategy](MIGRATION_STRATEGY.md) for complete inventory.

## Breaking Changes Highlights

> [!WARNING]
> All data attributes changed from `data-*` to `data-bs-*`. This affects every interactive component.

<details>
<summary><strong>Data Attributes (ALL CHANGED)</strong></summary>

```html
<!-- Bootstrap 4 -->
<button data-toggle="modal" data-target="#myModal">

<!-- Bootstrap 5 -->
<button data-bs-toggle="modal" data-bs-target="#myModal">
```

</details>

<details>
<summary><strong>Common Class Changes</strong></summary>

| Bootstrap 4 | Bootstrap 5 | Notes |
|-------------|-------------|-------|
| `.btn-block` | `.d-grid` wrapper | Structure change |
| `.form-group` | `.mb-3` | Use spacing utilities |
| `.custom-select` | `.form-select` | Renamed |
| `.close` | `.btn-close` | Renamed |
| `.ml-*` / `.mr-*` | `.ms-*` / `.me-*` | RTL support (start/end) |
| `.pl-*` / `.pr-*` | `.ps-*` / `.pe-*` | RTL support (start/end) |
| `.text-left` / `.text-right` | `.text-start` / `.text-end` | RTL support |
| `.no-gutters` | `.g-0` | Gutter utilities |
| `.font-weight-*` | `.fw-*` | Shortened |
| `.font-style-*` | `.fst-*` | Shortened |

</details>

<details>
<summary><strong>JavaScript API Changes</strong></summary>

```javascript
// Bootstrap 4 (jQuery required)
$('#myModal').modal('show');
$('[data-toggle="tooltip"]').tooltip();

// Bootstrap 5 (Vanilla JS - NO jQuery)
const modal = new bootstrap.Modal(document.getElementById('myModal'));
modal.show();

const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
tooltips.forEach(el => new bootstrap.Tooltip(el));
```

</details>

<details>
<summary><strong>Removed Components</strong></summary>

| Removed | Replacement |
|---------|-------------|
| `.media` | Flexbox utilities |
| `.jumbotron` | Utility classes |
| `.card-deck` | Grid system |
| jQuery dependency | Vanilla JS |

</details>

## Resources

### Official Bootstrap

| Resource | Link |
|----------|------|
| Migration Guide | [getbootstrap.com/docs/5.3/migration](https://getbootstrap.com/docs/5.3/migration/) |
| Documentation | [getbootstrap.com/docs/5.3](https://getbootstrap.com/docs/5.3/) |
| GitHub | [github.com/twbs/bootstrap](https://github.com/twbs/bootstrap) |

### Testing Tools

| Tool | Purpose | Link |
|------|---------|------|
| BackstopJS | Visual regression | [github.com/garris/BackstopJS](https://github.com/garris/BackstopJS) |
| Pa11y | Accessibility | [pa11y.org](https://pa11y.org/) |
| Lighthouse | Performance | [developers.google.com](https://developers.google.com/web/tools/lighthouse) |

### Migration Tools

| Tool | Description |
|------|-------------|
| [Bootstrap 5 Migrate Tool](https://github.com/coliff/bootstrap-5-migrate-tool) | Automated class replacement |
| [Bootstrap Diff](https://bootstrapdiff.com/) | Visual diff between versions |

### Y USA Resources

| Resource | Link |
|----------|------|
| Y USA Docs | [ds-docs.y.org](https://ds-docs.y.org) |
| GitHub Repository | [github.com/YCloudYUSA/yusaopeny-project](https://github.com/YCloudYUSA/yusaopeny-project) |
| Discussions | [github.com/YCloudYUSA/yusaopeny/discussions](https://github.com/YCloudYUSA/yusaopeny/discussions) |

## Success Metrics

### Technical

- [ ] All ~70 modules migrated to Bootstrap 5.3+
- [ ] Zero jQuery dependencies (except Drupal core)
- [ ] Lighthouse scores 90+
- [ ] Bundle size reduced 15-20%
- [ ] WCAG 2.2 AA compliance maintained
- [ ] Zero visual regressions

### Quality

- [ ] 100% templates use Bootstrap 5 syntax
- [ ] All interactive components functional
- [ ] Cross-browser compatibility verified
- [ ] Mobile responsive (including new `xxl` breakpoint)

### Business

- [ ] Zero site downtime
- [ ] Positive user feedback
- [ ] On-time delivery
- [ ] On-budget delivery

> [!NOTE]
> Track progress against these metrics throughout the migration. Update checkboxes as milestones are completed.

## FAQ

<details>
<summary><strong>Can we migrate just part of the site to Bootstrap 5?</strong></summary>

Yes, temporarily. We recommend the "Isolation" approach for Activity Finder, which allows the theme to use Bootstrap 5 while Activity Finder stays on Bootstrap 4 temporarily.

> [!WARNING]
> Maintaining two Bootstrap versions long-term is not recommended due to increased bundle size and maintenance overhead.

</details>

<details>
<summary><strong>How risky is this migration?</strong></summary>

**Medium risk overall.**

| Component | Risk Level | Reasoning |
|-----------|------------|-----------|
| Theme (openy_carnation) | 🟢 Low | Well-understood process |
| Layout Builder modules | 🟡 Medium | Many modules, but straightforward |
| Activity Finder | 🔴 High | BootstrapVue incompatibility |

Proper testing and staged rollout mitigate risk.

</details>

<details>
<summary><strong>What's the minimum viable migration?</strong></summary>

| Phase | Duration | Scope |
|-------|----------|-------|
| 1. Theme migration | 4-6 weeks | openy_carnation |
| 2. Activity Finder isolation | 2-3 weeks | Scoped CSS |
| 3. Basic testing | 2 weeks | Manual QA |
| **Total** | **~8-11 weeks** | Minimum viable |

</details>

<details>
<summary><strong>Can we skip some modules?</strong></summary>

**Yes!** If you're not using certain modules (e.g., ws_small_y, specific lb_* modules), you can skip them.

> [!TIP]
> Review your site's module inventory and identify which are actually in use before planning.

</details>

<details>
<summary><strong>What if we find bugs after rollout?</strong></summary>

We recommend a staged rollout to catch issues early:

1. **Internal** → Development/staging environment
2. **Beta** → Limited production users
3. **Production** → Full rollout

Maintain a rollback plan and provide support during rollout. Most issues should be caught during testing phases.

</details>

<details>
<summary><strong>How much will this cost?</strong></summary>

| Approach | Timeline | Team Size | Estimated Cost |
|----------|----------|-----------|----------------|
| Standard | 6-9 months | 2-3 devs | $150K-$250K |
| Aggressive | 3-4 months | 3-4 devs | $200K-$350K |
| Gradual | 9-12 months | 1-2 devs | $100K-$180K |

</details>

## Next Steps

| Step | Status | Action |
|------|--------|--------|
| 1 | ✅ | Read [MIGRATION_STRATEGY.md](MIGRATION_STRATEGY.md) |
| 2 | ⬜ | Complete [decisions/QUESTIONNAIRE.md](decisions/QUESTIONNAIRE.md) |
| 3 | ⬜ | Review with stakeholders |
| 4 | ⬜ | Get approval for timeline and resources |
| 5 | ⬜ | Begin Phase 1: Preparation |

## Questions?

| Channel | Link |
|---------|------|
| GitHub Issues | [yusaopeny-project/issues](https://github.com/YCloudYUSA/yusaopeny-project/issues) |
| GitHub Discussions | [yusaopeny/discussions](https://github.com/YCloudYUSA/yusaopeny/discussions) |
| Documentation | [ds-docs.y.org](https://ds-docs.y.org) |

---

| | |
|---|---|
| **Last Updated** | 19 December 2025 |
| **Document Version** | 1.1 |
| **Status** | Planning Phase |
