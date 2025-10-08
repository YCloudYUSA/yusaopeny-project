# Bootstrap 5 Migration - Questionnaire Responses

**Date Completed:** 2025-10-08
**Status:** COMPLETE
**Next Action:** Review Sprint Plan

---

## Response Summary

### Question 1: Timeline & Urgency
**Response:** **C - Gradual (9-12 months)**

**Rationale:**
- Lower priority, limited resources
- 1-2 developers available
- Sequential phases (one after another)
- Lowest risk approach

**Impact on Planning:**
- Sequential work (finish one phase before starting next)
- Longer timeline allows thorough testing
- Community has time to adapt

---

### Question 2: Activity Finder Migration Strategy
**Response:** **A - Temporary Isolation + Incremental Migration** ✅ RECOMMENDED

**Approach:**
1. **Phase 1 (2-3 weeks):** Scope Bootstrap 4 CSS to Activity Finder only
   - Theme can move forward with Bootstrap 5 immediately
   - Activity Finder keeps working on Bootstrap 4 temporarily
2. **Phase 2 (8-10 weeks later):** Gradually replace BootstrapVue components
   - Do this after theme and other modules are done
   - Replace BootstrapVue with Bootstrap 5 vanilla JS

**Rationale:**
- Very low risk, incremental approach
- Allows progress on everything else
- Fits perfectly with gradual timeline

**Impact on Planning:**
- Activity Finder isolated early (Sprint 3)
- Activity Finder full migration late (Sprints 18-21)
- Theme and modules can proceed without blocking

---

### Question 3: Resource Availability
**Response:**
- **Development:** B (1 Full-time Developer) + D (Part-time/Contractor support as needed)
- **QA/Testing:** B (Developers handle testing) → A (May add 1 QA Engineer later if budget allows)

**Resource Details:**
- **Primary:** 1 full-time developer throughout
- **Contractor:** Bring in for batch processing (WS modules, LB modules, Activity Finder Vue.js work)
- **QA:** Developers test their work initially, consider dedicated QA for Sprint 22 (comprehensive testing)

**Impact on Planning:**
- Sequential phases (1 developer)
- Contractor support for specific sprints (4-5, 8-11, 19-21)
- Testing infrastructure set up in Sprint 1, used throughout
- Comprehensive testing phase at end (Sprint 22)

---

### Question 4: Scope Priority
**Response:** **Priority Order:**
1. **Core Theme (openy_carnation)** - Foundation for everything
2. **Website Services Modules (ws_small_y, ws_event, ws_promotion, etc.)** - HIGH PRIORITY!
3. **Layout Builder Modules** - ALL critical (~20 modules)
4. **Content Type Modules** (y_branch, y_program, y_camp, etc.) - ALL critical
5. **Activity Finder Isolation** - Quick CSS scoping
6. **Activity Finder Full Migration** - Later phase

**Critical Components:**
- ALL Layout Builder modules needed
- ALL Content Type modules needed (see http://localhost:1313/docs/user-documentation/content-types/#content-types-library)
- All Website Services modules in use

**Impact on Planning:**
- ~70 components to migrate (comprehensive migration)
- Phase order follows priority
- No modules can be skipped
- WS modules prioritized higher than typical

---

### Question 5: Testing Requirements
**Response:** **C → A → B (Progressive Testing)**

**Testing Evolution:**
- **C - Basic (Early phases):** Manual testing by developers
  - Sprints 1-7 (Theme + WS modules)
- **A - Standard (Middle phases):** Visual regression + Accessibility
  - Sprints 8-21 (LB modules, Content Types, Activity Finder)
- **B - Comprehensive (Final phase):** Visual + Accessibility + Performance + Cross-browser
  - Sprint 22 (Final QA)

**Rationale:**
- Cost-effective
- Risk-appropriate (more testing for complex components)
- Fits gradual timeline

**Impact on Planning:**
- Testing infrastructure set up in Sprint 1
- Testing rigor increases over time
- Final comprehensive testing before rollout

---

### Question 6: Rollout Strategy
**Response:** **D → C → B → A (Multi-Tier Rollout)**

**Rollout Tiers:**
1. **D - New Sites First** → Bootstrap 5 for new builds only (zero risk to existing)
2. **C - Safe Sites Direct** → Deploy to 3-5 less complex, well-tested sites
3. **B - Activity Finder Flag** → Feature flag (leverage existing Bootstrap 3/4 config pattern)
4. **A - Distribution Staged** → Internal → Beta (5-10 sites) → Stable release

**Rationale:**
- Sophisticated, multi-tiered approach
- Minimizes risk at each stage
- Leverages existing feature flag infrastructure
- Perfect for distribution serving multiple sites

**Impact on Planning:**
- Rollout spans Sprint 24 (2 weeks)
- Requires coordination with community
- Feature flag implementation in Sprint 24
- Beta testing partners identified in Sprint 23

---

### Question 7: Breaking Changes Tolerance
**Response:** **D → B → C → A (Lean, Community-First Approach)**

**Breaking Changes Strategy:**
1. **D - Minimal/Community-Driven** → Start lean, community self-helps
2. **B - Documentation Only** → Add comprehensive docs as you encounter issues
3. **C - Compatibility Layer** → Add shims/polyfills if many sites struggle
4. **A - Migration Tools** → Build automated tools only if really needed

**Rationale:**
- Limited dev resources (1 dev + contractor)
- Gradual timeline (time for community to adapt)
- Feature flag rollout (sites opt-in when ready)
- Focus resources on actual migration, not tooling
- Iterate based on real feedback

**Impact on Planning:**
- Documentation in Sprint 23
- No upfront migration tool development
- Monitor community feedback
- Add tooling only if needed (post-Sprint 24)

---

### Question 8: Date Picker Replacement
**Response:** **Investigation Required** 🔍

**Options to Evaluate:**
- **A. Flatpickr** (recommended) - No Bootstrap dependency, lightweight, accessible
- **B. Tempus Dominus v6** - Built for Bootstrap 5, comprehensive, heavier
- **C. Native HTML5 Date Input** - Zero dependencies, limited styling

**Decision:** Sprint 1 investigation, implementation in Sprint 13

**Impact on Planning:**
- Sprint 1: Research and choose date picker
- Sprint 13: Implement chosen date picker in openy_repeat module
- Document decision in `decisions/DATE_PICKER_DECISION.md`

---

### Question 9: Success Criteria
**Response:**
- **Priority:** Technical Metrics > Operational Metrics > Quality Metrics
- **Failure Condition #1:** Community resistance/negative feedback
- **Failure Condition #2:** Major visual regressions on production sites

**Success = Technical Correctness First**
- All ~70 modules migrated to Bootstrap 5.3+
- No breaking production sites
- WCAG 2.2 AA accessibility maintained
- Timeline/budget respected (9-12 months)

**Failure = Community Trust Loss**
- Community resistance is #1 concern (maintaining distribution trust)
- Visual regressions break that trust
- This validates gradual, safe rollout strategy

**Impact on Planning:**
- Testing rigor justified (avoid regressions)
- Multi-tier rollout essential (avoid community resistance)
- Beta testing critical (catch issues before wide release)
- Documentation and communication prioritized

---

### Question 10: Long-Term Maintenance
**Response:**
- **Ongoing Maintenance:** C - Hybrid (Core team maintains + community contributes)
- **Bootstrap Updates:** B - Annual Review (check for updates yearly, not chasing every release)
- **Future Roadmap:** Considering Vite + TypeScript for frontend rendering + D (Undecided on long-term)

**Rationale:**
- Bootstrap 5 is a stepping stone, not the end goal
- Exploring modern tooling (Vite + TypeScript)
- Hybrid model is sustainable for distribution
- Annual updates are pragmatic

**Impact on Planning:**
- Don't over-invest in Bootstrap 5 migration tooling
- Bootstrap 5 is "good enough for now" while exploring alternatives
- Community ownership is important (hybrid model)
- Architecture should be flexible for future changes

---

## Summary Profile

**Bootstrap 5 Migration Approach:**

✅ **Gradual, Low-Risk, Community-Focused**

**Key Characteristics:**
- 9-12 months sequential timeline
- 1 developer + contractor support
- Progressive testing (Basic → Standard → Comprehensive)
- Multi-tier rollout (New → Safe → Flagged → Staged)
- Community-first support model
- All ~70 components migrated (comprehensive)
- Activity Finder isolated early, migrated late
- Hybrid long-term maintenance
- Exploring modern alternatives (Vite + TypeScript)

**Perfect for:** A distribution serving multiple production sites where community trust is paramount.

---

## Sprint Plan Summary

Based on these responses, the detailed sprint plan includes:

**24 Sprints over 48 weeks (~11 months):**
- **Phase 1:** Sprints 1-3 (6 weeks) - Preparation, Theme, Activity Finder Isolation
- **Phase 2:** Sprints 4-7 (8 weeks) - Website Services Modules
- **Phase 3:** Sprints 8-13 (12 weeks) - Layout Builder Modules
- **Phase 4:** Sprints 14-17 (8 weeks) - Content Type Modules
- **Phase 5:** Sprints 18-21 (8 weeks) - Activity Finder Migration
- **Phase 6:** Sprints 22-24 (6 weeks) - Testing, Documentation, Rollout

**Resource Allocation:**
- Primary developer: Full-time throughout (48 weeks)
- Contractor: ~8-10 sprints (batch processing, Vue.js work)
- QA Engineer: Optional for Sprint 22 (comprehensive testing)

---

## Next Steps

1. ✅ Review questionnaire responses (this document)
2. ✅ Review detailed sprint plan (`SPRINT_PLAN.md`)
3. ⬜ Get stakeholder approval
4. ⬜ Schedule Sprint 1 start date
5. ⬜ Assign primary developer
6. ⬜ Identify contractor resources
7. ⬜ Begin Phase 1!

---

## Approval

**Reviewed By:** _________________________________

**Approved By:** _________________________________

**Approval Date:** _________________________________

**Sprint 1 Start Date:** _________________________________

---

**Document Status:** COMPLETE ✅

**Related Documents:**
- [Migration Strategy](../MIGRATION_STRATEGY.md)
- [Sprint Plan](../SPRINT_PLAN.md)
- [Original Questionnaire](QUESTIONNAIRE.md)
