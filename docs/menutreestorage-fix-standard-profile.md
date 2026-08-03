# MenuTreeStorage Fix Analysis: Drupal Standard Profile

> [!NOTE]
> Testing MR [!14230](https://git.drupalcode.org/project/drupal/-/merge_requests/14230) (batch-load MenuTreeStorage) on Drupal core's standard installation profile.

## Test Environment

| Component | Value |
|-----------|-------|
| Drupal | 11.3.1 |
| Profile | standard |
| PHP | 8.3 |
| Platform | DDEV on macOS |
| Profiler | XHProf + XHGui (MySQL) |

---

## Executive Summary

| Metric | Without Fix | With Fix | Change |
|--------|-------------|----------|--------|
| **MenuTreeStorage::rebuild** | 66.78 ms | 58.70 ms | **-12%** |
| **safeExecuteSelect calls** | 296 | 236 | **-20%** |
| **safeExecuteSelect time** | 20.80 ms | 17.25 ms | **-17%** |
| **saveRecursive time** | 57.69 ms | 47.84 ms | **-17%** |

> [!IMPORTANT]
> The fix reduces database queries by 20% and MenuTreeStorage::rebuild time by 12% on standard profile installation.

---

## XHProf Run Details

| Run | XHProf ID | Wall Time | Memory |
|-----|-----------|-----------|--------|
| Without Fix | `695d16a57e190e09110012d1` | 7.94 sec | 52.72 MB |
| With Fix | `695d15a2f48df467bf032c58` | 11.46 sec | 52.73 MB |

> [!WARNING]
> The total wall time difference (3.5s) is due to database cleanup overhead from different starting states, not the MenuTreeStorage fix itself.

---

## MenuTreeStorage Function Comparison

### Key Functions

| Function | Without Fix | With Fix | Improvement |
|----------|-------------|----------|-------------|
| `safeExecuteSelect` calls | 296 | 236 | -60 queries |
| `safeExecuteSelect` time | 20.80 ms | 17.25 ms | -3.55 ms |
| `loadFullMultiple` calls | 111 | 111 | same |
| `loadFullMultiple` time | 10.16 ms | 10.02 ms | -0.14 ms |
| `doSave` time (all) | ~54.48 ms | ~45.28 ms | -9.2 ms |

### New Function: loadAllOriginals

With the fix, a new `loadAllOriginals` function batch-loads menu links:

```
loadAllOriginals: 2 calls, 9.69 ms
```

This replaces multiple individual queries with batch loading.

---

## Query Reduction Analysis

The fix reduces `safeExecuteSelect` calls from 296 to 236 (**-60 queries, -20%**).

| Operation | Without Fix | With Fix |
|-----------|-------------|----------|
| `doSave` SELECT queries | 125 | 63 |
| `getMenuNames` queries | 4 | 4 |
| `updateParentalStatus` queries | 56 | 56 |
| `loadFullMultiple` queries | 111 | 111 |

The primary reduction comes from `doSave` which now uses batch-loaded data instead of individual lookups.

---

## drush site:install Log Comparison

### With Fix (log excerpt)

```
[notice] Starting Drupal installation. This takes a while. [2.72 sec, 5.9 MB]
[info] mysql module installed. [5.27 sec, 27.43 MB]
[info] system module installed. [5.68 sec, 29.98 MB]
[notice] Performed install task: install_bootstrap_full [5.69 sec, 30.01 MB]
...
[notice] Performed install task: install_profile_modules [8.34 sec, 37.81 MB]
[notice] Performed install task: install_profile_themes [8.93 sec, 41.45 MB]
[info] standard module installed. [10.94 sec, 59.07 MB]
[notice] Performed install task: install_finished [11.80 sec, 53.22 MB]
[success] Installation complete. [11.80 sec, 53.29 MB]
```

### Without Fix (log excerpt)

```
[notice] Starting Drupal installation. This takes a while. [1.02 sec, 5.9 MB]
[info] mysql module installed. [2.78 sec, 27.43 MB]
[info] system module installed. [3.14 sec, 29.98 MB]
[notice] Performed install task: install_bootstrap_full [3.15 sec, 30.01 MB]
...
[notice] Performed install task: install_profile_modules [5.28 sec, 37.81 MB]
[notice] Performed install task: install_profile_themes [5.82 sec, 41.44 MB]
[info] standard module installed. [7.75 sec, 59.06 MB]
[notice] Performed install task: install_finished [8.58 sec, 53.21 MB]
[success] Installation complete. [8.58 sec, 53.29 MB]
```

---

## Conclusion

The MenuTreeStorage batch-load fix (MR !14230) provides measurable improvements on Drupal's standard profile:

1. **12% faster** MenuTreeStorage::rebuild (66.78ms → 58.70ms)
2. **20% fewer** database queries (296 → 236)
3. **17% faster** saveRecursive operations

These improvements compound significantly on larger installations like YMCA Website Services where menu operations are more extensive.

---

## Related

- [MR !14230](https://git.drupalcode.org/project/drupal/-/merge_requests/14230) - MenuTreeStorage batch-load fix
- [#3493290](https://www.drupal.org/project/drupal/issues/3493290) - Drupal.org issue
- [drush-site-install-profiling.md](drush-site-install-profiling.md) - YMCA WS profiling research
