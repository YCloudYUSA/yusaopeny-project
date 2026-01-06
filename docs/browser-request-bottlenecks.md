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

## Recommended Investigation

### High Impact

1. **[#2012800](https://www.drupal.org/project/drupal/issues/2012800)** - Lazy Element::children() in doRender
   - Directly addresses 1.97 sec Renderer bottleneck
   - Status: Needs work (stalled since 2021)

2. **[#3001284](https://www.drupal.org/project/drupal/issues/3001284)** - Cache tags for plugin derivers
   - Addresses DerivativeDiscoveryDecorator cache invalidation
   - Status: Needs work, Major priority

3. **[#2294569](https://www.drupal.org/project/drupal/issues/2294569)** - High memory consumption root cause
   - Meta-analysis by fabianx
   - May provide insights for plugin manager optimization

### Quick Wins

4. **[#3565604](https://www.drupal.org/project/drupal/issues/3565604)** - Remove unnecessary isset() in doRender
   - Status: RTBC, ready for merge
   - Minor impact but easy

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
