# Bootstrap 5 Migration Decision Questionnaire

**Purpose:** This questionnaire will help guide sprint planning and ensure the Bootstrap 5 migration aligns with your organization's priorities, resources, and risk tolerance.

**Instructions:** Please answer all 10 questions thoughtfully. Your responses will directly influence the migration timeline, technical approach, and resource allocation.

**Document Version:** 1.0
**Date:** 2025-10-08

---

## Question 1: Timeline & Urgency

**What is your preferred timeline for completing the Bootstrap 5 migration?**

- [ ] **A. Aggressive (3-4 months)** - High priority, willing to allocate significant resources
- [ ] **B. Standard (6-9 months)** - Balanced approach as outlined in strategy document
- [ ] **C. Gradual (9-12 months)** - Lower priority, limited resources
- [ ] **D. Flexible** - No fixed deadline, migrate when ready

**Follow-up:**
- Is there a specific business driver or deadline (e.g., Drupal 11 launch, conference demo, grant deadline)?
- Can phases run in parallel with adequate resources, or must they be sequential?

**Your Answer:**
```
[Write your answer here]
```

**Impact on Planning:**
- Aggressive timeline requires parallel work on Phases 3-5
- Gradual timeline allows sequential, lower-risk approach
- This affects resource needs and sprint structure

---

## Question 2: Activity Finder Migration Strategy

**Which approach do you prefer for migrating the Activity Finder system?**

⚠️ **Critical Context:** BootstrapVue 2 is NOT compatible with Bootstrap 5. This is our most complex decision.

**Options:**

- [ ] **A. Temporary Isolation + Incremental Migration** _(RECOMMENDED)_
  - Start: Scope Bootstrap 4 CSS to Activity Finder only (2-3 weeks)
  - Then: Gradually replace BootstrapVue with Bootstrap 5 vanilla JS (8-10 weeks)
  - **Pros:** Lower risk, incremental, allows theme to move forward immediately
  - **Cons:** Technical debt during transition, maintains two Bootstrap versions temporarily
  - **Total Timeline:** 10-13 weeks

- [ ] **B. Direct Bootstrap 5 Migration (No Isolation)**
  - Immediately migrate Activity Finder to Bootstrap 5 without BootstrapVue
  - **Pros:** Clean approach, no technical debt
  - **Cons:** Higher risk, blocks theme migration, longer timeline
  - **Total Timeline:** 8-10 weeks (but blocks other work)

- [ ] **C. Migrate to BootstrapVueNext (Vue 3 + Bootstrap 5)**
  - Complete rewrite using BootstrapVueNext (currently in alpha)
  - **Pros:** Modern stack, long-term solution, Vue 3 benefits
  - **Cons:** Alpha software, highest risk, most expensive, Vue 3 migration required
  - **Total Timeline:** 12-16 weeks

- [ ] **D. Keep Activity Finder on Bootstrap 4 Permanently**
  - Maintain separate Bootstrap 4 CSS indefinitely
  - **Pros:** No migration cost, zero risk to Activity Finder
  - **Cons:** Technical debt, maintenance burden, inconsistent stack
  - **Total Timeline:** 2-3 weeks (isolation only)

**Your Answer:**
```
[Write your answer here - include reasoning]
```

**Follow-up Questions:**
- How critical is the Activity Finder to your operations? (High/Medium/Low)
- Can you accept temporary CSS scoping during migration?
- Are you interested in Vue 3 for future features?
- What's your risk tolerance for Activity Finder changes?

**Your Answers:**
```
[Write your answers here]
```

**Impact on Planning:**
- Option A: Allows parallel theme and Activity Finder work
- Option B: Blocks theme migration until Activity Finder complete
- Option C: Adds Vue 3 migration to scope (significant work)
- Option D: Simplest but maintains technical debt

---

## Question 3: Resource Availability

**What resources can you allocate to the Bootstrap 5 migration?**

**Development Resources:**
- [ ] **A. 3+ Full-time Developers** (Allows aggressive parallel work)
- [ ] **B. 2 Full-time Developers** (Standard approach)
- [ ] **C. 1 Full-time Developer** (Sequential approach, longer timeline)
- [ ] **D. Part-time/Contractor Resources** (Specify: _______ hours/week)
- [ ] **E. Uncertain/Need to discuss**

**QA/Testing Resources:**
- [ ] **A. Dedicated QA Team (2+ people)**
- [ ] **B. 1 QA Engineer**
- [ ] **C. Developers handle testing**
- [ ] **D. Community testing only**

**Other Resources:**
- Technical Writer: [ ] Yes [ ] No [ ] Part-time
- Project Manager: [ ] Yes [ ] No [ ] Part-time
- DevOps Support: [ ] Yes [ ] No [ ] Part-time

**Your Answer:**
```
[Describe available resources and constraints]
```

**Impact on Planning:**
- 3+ developers: Can run Phases 3, 4, and 5 in parallel (6-month timeline)
- 2 developers: Mixed parallel/sequential approach (7-8 month timeline)
- 1 developer: Sequential only (12+ month timeline)
- QA resources affect testing phase duration

---

## Question 4: Scope Priority

**Which components are MOST CRITICAL to migrate first?**

**Rank these in order of priority (1 = highest, 5 = lowest):**

- [ ] **___ Core Theme (openy_carnation)** - Affects entire site
- [ ] **___ Activity Finder System** - Key functionality
- [ ] **___ Layout Builder Modules (lb_*)** - Content building blocks
- [ ] **___ Content Type Modules (y_branch, y_program, etc.)** - Specific content types
- [ ] **___ Website Services Modules (ws_small_y, etc.)** - Extended functionality

**Are there specific modules that MUST be migrated first?**
```
[List specific modules and why they're critical]
```

**Are there modules that can wait or be skipped?**
```
[List modules that are low priority or unused]
```

**Impact on Planning:**
- Determines sprint priorities
- Affects which modules get migrated in parallel
- May allow skipping unused modules entirely

---

## Question 5: Testing Requirements

**What level of testing do you require?**

**Automated Testing:**
- [ ] **A. Comprehensive** (Visual regression, accessibility, performance, functional)
- [ ] **B. Standard** (Visual regression + accessibility)
- [ ] **C. Basic** (Manual testing only)
- [ ] **D. Minimal** (Smoke testing)

**Manual Testing:**
- [ ] **A. Full Cross-Browser** (Chrome, Firefox, Safari, Edge + Mobile)
- [ ] **B. Primary Browsers Only** (Chrome + Mobile)
- [ ] **C. Chrome Only**

**Accessibility Testing:**
- [ ] **A. WCAG 2.2 AA Compliance Required** (Pa11y + screen reader testing)
- [ ] **B. Basic Accessibility Check** (Automated tools only)
- [ ] **C. Not Required** (Best effort)

**Beta Testing:**
- [ ] **A. Yes, with partner sites** (Specify how many: _____)
- [ ] **B. Internal testing only**
- [ ] **C. No beta testing**

**Your Answer:**
```
[Describe testing approach and requirements]
```

**Impact on Planning:**
- Comprehensive testing adds 4-6 weeks to Phase 7
- Basic testing reduces to 2-3 weeks
- Beta testing requires coordination and support

---

## Question 6: Rollout Strategy

**How do you want to roll out Bootstrap 5 to production sites?**

- [ ] **A. Staged Rollout** _(RECOMMENDED)_
  - Week 1-2: Internal testing
  - Week 3-4: Beta partner sites (5-10 sites)
  - Week 5-6: Stable release to all sites
  - **Pros:** Lower risk, collect feedback, fix issues before wide release
  - **Cons:** Takes longer, requires coordination

- [ ] **B. Flag-Based Rollout**
  - Add feature flag to enable Bootstrap 5 per-site
  - Sites opt-in when ready
  - **Pros:** Maximum flexibility, sites control timing
  - **Cons:** Maintains two codepaths, more complex

- [ ] **C. Big Bang Release**
  - Release Bootstrap 5 to all sites at once
  - **Pros:** Simplest, fastest
  - **Cons:** Higher risk, all sites affected simultaneously

- [ ] **D. New Sites Only**
  - Bootstrap 5 for new sites, existing sites stay on Bootstrap 4
  - **Pros:** Zero risk to existing sites
  - **Cons:** Maintains two codepaths indefinitely, technical debt

**Your Answer:**
```
[Describe preferred rollout approach]
```

**Follow-up:**
- Do you have partner sites willing to be beta testers?
- What's your tolerance for bugs in production?
- Can you provide support for sites during rollout?

**Your Answers:**
```
[Write your answers here]
```

**Impact on Planning:**
- Staged rollout adds 3-4 weeks but reduces risk
- Flag-based rollout adds development complexity
- Big bang requires more thorough testing upfront

---

## Question 7: Breaking Changes Tolerance

**How will you handle breaking changes for existing custom code?**

**Context:** Bootstrap 5 has many breaking changes. Sites with custom CSS/JS may need updates.

- [ ] **A. Proactive Migration** _(RECOMMENDED)_
  - Provide automated migration tools
  - Document all breaking changes clearly
  - Offer migration support/webinars
  - Create upgrade guides for common patterns
  - **Effort:** High upfront, easier for community

- [ ] **B. Reactive Support**
  - Release with documentation
  - Support sites as issues arise
  - **Effort:** Lower upfront, more support burden later

- [ ] **C. Community-Driven**
  - Release with basic documentation
  - Community helps each other
  - **Effort:** Minimal upfront, relies on community

- [ ] **D. Maintain Compatibility Layer**
  - Add shims/polyfills for Bootstrap 4 class names
  - Gradual deprecation over time
  - **Effort:** High development cost, easier migration for community

**Your Answer:**
```
[Describe approach to breaking changes]
```

**Will you provide:**
- [ ] Migration workshops/webinars
- [ ] Office hours for support
- [ ] Custom migration scripts
- [ ] Video tutorials
- [ ] Sample code/examples

**Impact on Planning:**
- Proactive approach requires documentation phase (2-3 weeks)
- Compatibility layer adds development time (3-4 weeks)
- Support strategy affects Phase 8 duration

---

## Question 8: Budget & Cost Considerations

**What are your budget considerations for this migration?**

**Development Budget:**
- [ ] **A. Fully Funded** - Budget approved, resources available
- [ ] **B. Limited Budget** - Need to minimize costs
- [ ] **C. Seeking Funding** - Grant/fundraising in progress
- [ ] **D. Volunteer/Community Effort**

**Estimated Cost (based on timeline/resources):**
- Standard approach (6-9 months, 2-3 developers): ~$150,000-$250,000
- Aggressive approach (3-4 months, 3-4 developers): ~$200,000-$350,000
- Gradual approach (9-12 months, 1-2 developers): ~$100,000-$180,000

**Cost-Saving Options:**
- [ ] Skip low-priority modules
- [ ] Reduce testing scope
- [ ] Limit documentation
- [ ] No beta testing
- [ ] Community contributions

**Your Answer:**
```
[Describe budget situation and constraints]
```

**Impact on Planning:**
- Budget determines resource allocation
- May require phased funding approach
- Affects timeline and scope decisions

---

## Question 9: Success Criteria

**How will you measure success of the Bootstrap 5 migration?**

**Select your top 3-5 success criteria:**

- [ ] All modules successfully migrated to Bootstrap 5
- [ ] Zero visual regressions in production
- [ ] Improved performance metrics (Lighthouse scores)
- [ ] Maintained/improved accessibility (WCAG 2.2 AA)
- [ ] Positive community feedback
- [ ] Reduced bundle sizes (15-20% smaller)
- [ ] Improved developer experience
- [ ] On-time delivery
- [ ] On-budget delivery
- [ ] No site downtime during migration
- [ ] Smooth rollout with minimal support issues
- [ ] High adoption rate among partner sites
- [ ] Improved maintainability for future
- [ ] Other: _________________________________

**What would constitute "failure"?**
- [ ] Major bugs in production
- [ ] Activity Finder breaks
- [ ] Significant visual regressions
- [ ] Accessibility issues
- [ ] Timeline delays >3 months
- [ ] Budget overruns >25%
- [ ] Negative community feedback
- [ ] Low adoption rate (<50% within 6 months)
- [ ] Other: _________________________________

**Your Answer:**
```
[Describe success criteria and failure conditions]
```

**Impact on Planning:**
- Success criteria influence sprint goals
- Failure conditions set risk thresholds
- Determines testing and QA requirements

---

## Question 10: Long-Term Maintenance

**How will Bootstrap 5 be maintained after migration?**

**Ongoing Maintenance:**
- [ ] **A. Dedicated Team** - Permanent resources for theme/module maintenance
- [ ] **B. Community-Driven** - Community maintains and contributes
- [ ] **C. Contractor Support** - Periodic contractor engagements
- [ ] **D. Minimal Maintenance** - Fix critical bugs only

**Bootstrap Updates:**
- [ ] **A. Stay Current** - Update to latest Bootstrap 5.x regularly
- [ ] **B. Annual Updates** - Review and update yearly
- [ ] **C. As Needed** - Update only when necessary (security, bugs)

**Future Migrations:**
- [ ] **A. Plan for Bootstrap 6** - Be ready for next major version
- [ ] **B. Stick with Bootstrap 5** - No plans to upgrade beyond Bootstrap 5
- [ ] **C. Consider Alternatives** - May move away from Bootstrap

**Your Answer:**
```
[Describe long-term maintenance strategy]
```

**Follow-up:**
- Who will own the Bootstrap 5 codebase after migration?
- How will community contributions be managed?
- What's the process for updates and patches?

**Your Answers:**
```
[Write your answers here]
```

**Impact on Planning:**
- Maintenance strategy affects architecture decisions
- Update frequency influences technical choices
- Determines documentation needs

---

## Summary of Responses

**Please summarize your key decisions:**

1. **Timeline:** _________________________________________________
2. **Activity Finder Approach:** ___________________________________
3. **Available Resources:** _________________________________________
4. **Priority Components:** _________________________________________
5. **Testing Level:** ______________________________________________
6. **Rollout Strategy:** ____________________________________________
7. **Breaking Changes:** ___________________________________________
8. **Budget:** _____________________________________________________
9. **Success Criteria:** ____________________________________________
10. **Maintenance Plan:** ___________________________________________

---

## Next Steps

Once this questionnaire is complete:

1. **Review Responses** with stakeholders
2. **Finalize Sprint Plan** based on decisions
3. **Allocate Resources** according to answers
4. **Create Detailed Schedule** with milestones
5. **Get Approval** to proceed
6. **Begin Phase 1** (Preparation & Research)

---

## Sprint Planning Recommendations

Based on typical responses, here are recommended sprint structures:

### Scenario A: Aggressive Timeline (3-4 months)
- **Resources:** 3-4 developers, 2 QA engineers
- **Approach:** All phases in parallel after Phase 2
- **Sprints:** 2-week sprints, 6-8 total
- **Activity Finder:** Option A (Isolation + Migration)
- **Risk:** MEDIUM-HIGH

### Scenario B: Standard Timeline (6-9 months)
- **Resources:** 2-3 developers, 1-2 QA engineers
- **Approach:** Mix of parallel and sequential phases
- **Sprints:** 2-week sprints, 12-18 total
- **Activity Finder:** Option A (Isolation + Migration)
- **Risk:** LOW-MEDIUM

### Scenario C: Gradual Timeline (9-12 months)
- **Resources:** 1-2 developers, community testing
- **Approach:** Sequential phases
- **Sprints:** 2-week sprints, 18-24 total
- **Activity Finder:** Option D (Keep Bootstrap 4) initially
- **Risk:** LOW

---

## Document Control

**Status:** [ ] Draft [ ] Under Review [ ] Approved

**Completed By:** _________________________________

**Date Completed:** _________________________________

**Reviewed By:** _________________________________

**Approved By:** _________________________________

**Approval Date:** _________________________________

---

## Notes & Comments

Use this space for additional notes, concerns, or questions:

```
[Your notes here]
```

---

**End of Questionnaire**

Please save your completed questionnaire as:
`docs/bootstrap-5-migration/decisions/QUESTIONNAIRE_COMPLETED_[DATE].md`
