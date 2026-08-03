# Drush Site Install Profiling Research

> [!NOTE]
> This research compares `drush site:install` performance between Drupal 11.1.9, Drupal 11.3.1, and Drupal 11.3.1 with profile batching fix on YMCA Website Services.

## Executive Summary

| Metric | Drupal 11.1 | Drupal 11.3 | 11.3 + Profile Fix | 11.3 + All Fixes | Best Improvement |
|--------|-------------|-------------|---------------------|------------------|------------------|
| **Total Time** | 537 sec (9 min) | 339 sec (5.6 min) | 180 sec (3 min) | **152 sec (2.5 min)** | **72% faster** |
| **Peak Memory** | 4.38 GB | 955 MB | 326 MB | **325 MB** | **93% less** |
| Container Rebuilds | 282 | 147 | ~7 | ~7 | **97% fewer** |
| Menu Queries | 245K | 230K | 230K | **114K** | **53% fewer** |

> [!IMPORTANT]
> **Drupal 11.3 + profile batching + MenuTreeStorage fix delivers: 72% faster than 11.1, 55% faster than 11.3 alone.**

---

## Key Metrics Comparison (XHProf Data)

| Metric | Drupal 11.1 | Drupal 11.3 | Improvement |
|--------|-------------|-------------|-------------|
| ModuleInstaller::install calls | 265 | 258 | 3% fewer |
| Kernel updates | 279 | 153 | **45% fewer** |
| Container rebuilds | 282 | 147 | **48% fewer** |
| StaticReflectionParser::parse | 69,093 | 36,853 | **47% fewer** |
| Reflection memory | 13,038 MB | 6,252 MB | **52% less** |

---

## Memory Growth Comparison

```mermaid
xychart-beta
    title "Memory Usage During Installation"
    x-axis [1, 50, 100, 150, 200, 249]
    y-axis "Memory (MB)" 0 --> 4000
    bar [25, 65, 123, 776, 1920, 3500]
    line [28, 58, 92, 219, 514, 829]
```

### Drupal 11.1 Memory Growth

| Module # | Time | Memory | Growth Rate |
|----------|------|--------|-------------|
| 1 | 2.6s | 25 MB | baseline |
| 50 | 29s | 65 MB | +0.8 MB/module |
| 100 | 77s | 123 MB | +1.2 MB/module |
| 150 | 148s | 776 MB | **+13 MB/module** |
| 200 | 261s | 1.92 GB | **+23 MB/module** |
| 249 | 426s | 3.5 GB | **+32 MB/module** |
| **Final** | **537s** | **4.38 GB** | - |

### Drupal 11.3 Memory Growth

| Module # | Time | Memory | Growth Rate |
|----------|------|--------|-------------|
| 1 | 3.7s | 28 MB | baseline |
| 50 | 8.4s | 58 MB | +0.6 MB/module |
| 100 | 15s | 92 MB | +0.7 MB/module |
| 150 | 49s | 219 MB | +2.5 MB/module |
| 200 | 144s | 514 MB | +5.9 MB/module |
| 249 | 280s | 829 MB | +6.4 MB/module |
| **Final** | **339s** | **955 MB** | - |

> [!TIP]
> Memory growth in 11.3 stays **linear** vs **exponential** in 11.1!

---

## Why 11.3 Is Faster

<details>
<summary><strong>Technical explanation</strong></summary>

### The Problem in Drupal 11.1

In Drupal 11.1, `ModuleInstaller::install()` processes modules **one at a time**:

```php
// Drupal 11.1 - core/lib/Drupal/Core/Extension/ModuleInstaller.php
foreach ($module_list as $module) {
    // Installs ONE module
    // Rebuilds container EACH TIME
    $this->updateKernel($module_filenames);
}
```

**Result**: 265 modules → 279 container rebuilds → 69,093 reflection parser calls

### The Solution in Drupal 11.2+

Drupal 11.2 introduced batched installation via [#3416522](https://www.drupal.org/project/drupal/issues/3416522):

```php
// Drupal 11.3 - core/lib/Drupal/Core/Extension/ModuleInstaller.php
$module_groups = [];
foreach ($module_list as $module) {
    $module_groups[$index][] = $module;
    // Group up to 20 modules together
    if (count($module_groups[$index]) === Settings::get('core.multi_module_install_batch_size', 20)) {
        $index++;
    }
}

// Install each GROUP with single container rebuild
foreach ($module_groups as $modules) {
    $this->doInstall($modules, ...);
}
```

**Result**: 258 modules → 147 container rebuilds → 36,853 reflection parser calls

</details>

---

## Profile Batching Fix

> [!WARNING]
> The openy profile's `openy_enable_module` function was calling `ModuleInstaller::install()` **121 times separately**, completely bypassing Drupal 11.3's batching optimization.

### The Problem

```php
// openy.profile - BEFORE fix
function openy_install_features(array &$install_state) {
    foreach ($modules as $module) {
        // Creates 121 separate batch operations!
        $module_operations[] = ['openy_enable_module', (array) $module];
    }
}

function openy_enable_module($module_name) {
    // Called 121 times, each triggering container rebuild
    $service->install([$module_name]);
}
```

### The Fix

```php
// openy.profile - AFTER fix
function openy_install_features(array &$install_state) {
    if (version_compare(\Drupal::VERSION, '11.2', '>=')) {
        $batch_size = Settings::get('openy.module_install_batch_size',
            Settings::get('core.multi_module_install_batch_size', 20));
        // Groups modules into batches of 20
        foreach (array_chunk($modules, $batch_size) as $chunk) {
            $module_operations[] = ['openy_enable_modules', [$chunk]];
        }
    }
}

function openy_enable_modules(array $module_names) {
    // Called ~6 times with 20 modules each
    $service->install($module_names);
}
```

**Result**: 121 separate installs → ~7 batched installs → **47% faster, 66% less memory**

---

## Top Memory Consumers

### Drupal 11.1

| Function | Calls | Memory (MB) |
|----------|-------|-------------|
| `StaticReflectionParser::parse` | 69,093 | 13,038 |
| `TokenParser::__construct` | 67,807 | 12,419 |
| `Connection::query` | 331,108 | 6,864 |
| `StatementWrapperIterator::execute` | 360,648 | 6,745 |
| `MenuTreeStorage::safeExecuteSelect` | 245,444 | 4,616 |

### Drupal 11.3

| Function | Calls | Memory (MB) |
|----------|-------|-------------|
| `TokenParser::__construct` | 36,853 | 6,641 |
| `Connection::query` | 303,698 | 6,361 |
| `StaticReflectionParser::parse` | 36,853 | 6,252 |
| `StatementWrapperIterator::execute` | 331,987 | 6,234 |
| `MenuTreeStorage::safeExecuteSelect` | 230,127 | 4,384 |

> [!NOTE]
> Reflection parser calls dropped by **47%** (69,093 → 36,853)

---

## Recommendations

### 1. Upgrade to Drupal 11.2+ (Completed in drupal113 branch)

- Batched module installation works automatically
- Core improvement: **37% faster install, 78% less memory**

### 2. Apply Profile Batching Fix

The `openy.profile` fix batches module installation at the profile level:

- Reduces `ModuleInstaller::install()` calls from 121 to ~7
- Combined with core: **67% faster, 93% less memory**

### 3. Consider Tuning Batch Size

Add to `settings.php` for different batch sizes:

```php
// Default is 20, increase for faster systems with more RAM
$settings['core.multi_module_install_batch_size'] = 30;

// Or use profile-specific setting
$settings['openy.module_install_batch_size'] = 30;
```

### 4. MenuTreeStorage Optimization (Drupal Core Issue #3493290)

> [!TIP]
> **Additional 55% speedup achieved** by batch-loading menu links during `rebuild()`.

#### The Problem

`MenuTreeStorage::rebuild()` queries database **per-link** to check if link exists:

```php
// BEFORE: One query per menu link
protected function doSave(array $link) {
    $query = $this->connection->select($this->table);
    $query->condition('id', $link['id']);
    $original = $this->safeExecuteSelect($query)->fetchAssoc(); // 230K calls!
}
```

#### The Fix

Pre-load all existing links at start of `rebuild()`:

```php
// AFTER: One query for ALL links
public function rebuild(array $definitions) {
    $this->preloadedOriginals = $this->loadAllOriginals(array_keys($definitions));
    // ... proceed with rebuild using cached data
}
```

#### Results

| Function | Before (calls) | After (calls) | Before (sec) | After (sec) | Improvement |
|----------|----------------|---------------|--------------|-------------|-------------|
| `safeExecuteSelect` | 230,127 | 113,787 | 16.16 | 8.71 | **-46%** |
| `MenuTreeStorage::doSave` | 77,763 | 57,779 | 22.24 | 11.98 | **-46%** |
| `MenuLinkManager::rebuild` | 181 | 180 | 49.57 | 22.06 | **-56%** |
| `ModuleInstaller::install` | 131 | 18 | 275.85 | 98.53 | **-64%** |
| **Total (`main()`)** | 1 | 1 | **338.86** | **151.69** | **-55%** |

| Metric | Before | After | Saved |
|--------|--------|-------|-------|
| **Total Time** | 338.86 sec | 151.69 sec | **187 sec (55%)** |
| **Peak Memory** | 954 MB | 325 MB | **629 MB (66%)** |

> [!NOTE]
> See [Drupal Core Issue #3493290](https://www.drupal.org/project/drupal/issues/3493290) for the upstream patch.

### 5. Future Investigation

- `ModuleHandler::invokeAllWith` time increased in 11.3 (investigate hooks)

---

## Profiling Data

Raw logs available in [`docs/profiling-logs/`](profiling-logs/):

| File | Description |
|------|-------------|
| [`drush_si_profiling_standard_11.1.log`](profiling-logs/drush_si_profiling_standard_11.1.log) | Drupal 11.1.9 install log |
| [`drush_si_profiling_standard_11_3.log`](profiling-logs/drush_si_profiling_standard_11_3.log) | Drupal 11.3.1 install log |
| [`drush_si_profiling_standard_11_3_fix1.log`](profiling-logs/drush_si_profiling_standard_11_3_fix1.log) | Drupal 11.3.1 + profile batching fix |
| [`drush_si_profiling_standard_11_3_menutree_fix.log`](profiling-logs/drush_si_profiling_standard_11_3_menutree_fix.log) | Drupal 11.3.1 + profile batching + MenuTreeStorage fix |

---

## Related Issues

- [Drupal #3416522: Add ability to install multiple modules with single container rebuild](https://www.drupal.org/project/drupal/issues/3416522)
- [Drupal #3493290: Reduce database queries in MenuTreeStorage::rebuild()](https://www.drupal.org/project/drupal/issues/3493290)
- [Drupal #3395260: Performance improvements for AttributeClassDiscovery](https://www.drupal.org/project/drupal/issues/3395260)
- [Drupal #3473563: Change record for container_rebuild_required](https://www.drupal.org/node/3473563)

---

## Environment

| Component | Version |
|-----------|---------|
| YMCA Website Services | 4.0 |
| PHP | 8.3 |
| Profiler | XHProf + XHGui |
| Platform | DDEV on macOS (M3 Pro) |
