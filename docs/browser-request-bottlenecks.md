# Browser Request Performance Bottlenecks Research

> [!NOTE]
> This research identifies performance bottlenecks in YMCA Website Services browser requests using XHProf profiling.

## Executive Summary

| Metric | Cold Cache | Notes |
|--------|------------|-------|
| **Total Time** | 2.90 sec | First page load after install |
| **Peak Memory** | 97 MB | Homepage render |

---

## Key Bottlenecks Identified

### 1. Plugin Discovery (`DefaultPluginManager::getDefinitions`)

| Function | Time | Calls | Impact |
|----------|------|-------|--------|
| `DefaultPluginManager::getDefinitions` | 1.30 sec | 244 | **45% of request** |
| `DefaultPluginManager::findDefinitions` | 1.27 sec | 32 | Plugin scanning |
| `DerivativeDiscoveryDecorator::getDefinitions` | 1.00 sec | 31 | Derivative loading |

### 2. Rendering (`Renderer::doRender`)

| Function | Time | Calls | Impact |
|----------|------|-------|--------|
| `Renderer::doRender` | 1.97 sec | 11 | **68% of request** |
| `ThemeManager::render` | 1.09 sec | 3 | Theme processing |
| `TwigThemeEngine::renderTemplate` | 1.09 sec | 5 | Template compilation |

---

## Drupal.org Related Issues

### Plugin Discovery / Caching

| Issue | Title | Status | Priority |
|-------|-------|--------|----------|
| [#2294569](https://www.drupal.org/project/drupal/issues/2294569) | Determine cause of high memory consumption | Postponed | **Major** |
| [#2046565](https://www.drupal.org/project/drupal/issues/2046565) | Cache the local action plugins that appear per route | Postponed | Normal |
| [#2553695](https://www.drupal.org/project/drupal/issues/2553695) | Cache EditorManager::getAttachments() | Needs work | **Major** |
| [#3037985](https://www.drupal.org/project/drupal/issues/3037985) | Queue processors are leaking memory | Postponed | **Major** |

### DerivativeDiscoveryDecorator

| Issue | Title | Status | Priority |
|-------|-------|--------|----------|
| [#3001284](https://www.drupal.org/project/drupal/issues/3001284) | Allow plugin derivers to specify cache tags | Needs work | **Major** |
| [#2880682](https://www.drupal.org/project/drupal/issues/2880682) | Derived discovery can result in rebuilding out of date data | Needs work | Minor |
| [#3448540](https://www.drupal.org/project/drupal/issues/3448540) | DerivativeDiscoveryDecorator not supporting object definitions | Active | **Major** |

### Renderer::doRender

| Issue | Title | Status | Priority |
|-------|-------|--------|----------|
| [#2012800](https://www.drupal.org/project/drupal/issues/2012800) | Renderer::doRender() could be lazier about calling Element::children() | Needs work | Normal |
| [#3565604](https://www.drupal.org/project/drupal/issues/3565604) | Remove unnecessary isset() from Renderer::doRender() | **RTBC** | Minor |
| [#3507959](https://www.drupal.org/project/drupal/issues/3507959) | Refactor render context stack to avoid static property | Active | Normal |
| [#2490981](https://www.drupal.org/project/drupal/issues/2490981) | Fabianx' Drupal 8 performance master plan | Active | Minor |

---

## Deep Dive: Issue Analysis

### Quick Win: #3565604 (RTBC)

**Remove unnecessary isset() from Renderer::doRender()**

<details>
<summary>Patch Details</summary>

Removes redundant `if (isset($elements))` check around XSS sanitization since `$elements` is a required parameter. Also removes corresponding PHPStan baseline entry.

```php
// Before: unnecessary check
if (isset($elements)) {
  // sanitize markup keys
}

// After: direct execution
// sanitize markup keys
```

</details>

- **Impact**: Minor code cleanup
- **Risk**: None - straightforward refactor
- **Action**: Will be merged soon (created by catch)

---

### High Impact: #2012800

**Renderer::doRender() could be lazier about calling Element::children()**

<details>
<summary>Technical Details</summary>

**Problem**: `Element::children()` sorts elements by weight but result is unused when `#theme` is set. This wastes CPU on every themed element render.

**Performance Data**: Testing with 50 threaded comments showed:
- Wall time decreased by 0.3%
- CPU time decreased by 0.5%

**Patch**: `2012800-37.patch` (April 2023, Drupal 10.1.x)

</details>

- **Impact**: 0.3-0.5% improvement per render
- **Status**: Needs work - requires re-profiling for 11.x
- **Action**: Consider testing patch in YMCA WS

---

### Architectural: #3001284

**Allow plugin derivers to specify cache tags**

<details>
<summary>Context</summary>

Currently, external code must manually clear plugin caches when source data changes (e.g., when menu entities change, block plugin cache is cleared separately). This issue proposes letting derivers specify cache tags for automatic invalidation.

**Maintainer Concerns**:
- Some derivative patterns may be deprecated
- Cache tags have performance costs
- Valid use cases remain (e.g., Group module)

</details>

- **Impact**: Improved cache invalidation
- **Status**: Postponed - architectural concerns
- **Action**: Monitor for progress

---

### Meta: #3492233 (Major)

**[meta] Reduce memory/cpu/io cost of attribute discovery**

Active umbrella issue tracking multiple related efforts:

| Child Issue | Description | Status |
|-------------|-------------|--------|
| [#3486503](https://www.drupal.org/project/drupal/issues/3486503) | Database-backed FileCache | Needs work |
| [#3416522](https://www.drupal.org/project/drupal/issues/3416522) | Multi-module install | **Committed** |
| Container rebuild optimization | Various | In progress |

---

### Reference: #2294569 (Memory Analysis)

**Key Findings from fabianx's Analysis:**

| Component | Memory Usage |
|-----------|--------------|
| Loaded classes (with opcache) | 8-17 MB |
| Views configuration | 24.25 MB |
| Container compilation | 13 MB |
| DefaultPluginManager static | **3.9 MB** |
| YAML file loader | 2 MB |

**Recommended Optimizations**:
1. Disable caches during installation (35s → 9s)
2. Use FileCache for annotations
3. Pre-generate container and plugin definitions
4. Optimize Config::save schema validation

---

## Recommended Investigation

### Immediate Actions

1. **[#3565604](https://www.drupal.org/project/drupal/issues/3565604)** - Will merge soon
   - No action needed, monitoring only

2. **[#2012800](https://www.drupal.org/project/drupal/issues/2012800)** - Test patch
   - Download and test `2012800-37.patch`
   - Profile before/after with XHProf

### Future Consideration

3. **[#3492233](https://www.drupal.org/project/drupal/issues/3492233)** - Attribute discovery meta
   - Track child issues for potential patches
   - Database-backed FileCache when ready

---

## Environment

| Component | Version |
|-----------|---------|
| YMCA Website Services | 4.0 |
| Drupal | 11.3.1 |
| PHP | 8.3 |
| Profiler | XHProf + XHGui |
| Platform | DDEV on macOS |

---

## Related

- [drush-site-install-profiling.md](drush-site-install-profiling.md) - Installation performance research
- [Drupal #3493290](https://www.drupal.org/project/drupal/issues/3493290) - MenuTreeStorage batch-load fix (applied)
