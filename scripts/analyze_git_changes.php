#!/usr/bin/env php
<?php
/**
 * Script to analyze git status and git diff in all module/profile folders with a .git directory.
 * Only presents packages with write access for interactive actions.
 * Usage: php scripts/analyze_git_changes.php [--interactive|-i]
 */

require_once __DIR__ . '/git_access_util.php';
require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Dotenv\Dotenv;

const MODULES_CONTRIB = __DIR__ . '/../docroot/modules/contrib';
const PROFILES_CONTRIB = __DIR__ . '/../docroot/profiles/contrib';
const COMPOSER_LOCK = __DIR__ . '/../composer.lock';
const ACCESS_CACHE_FILE = __DIR__ . '/../repo_access_cache.json';

$options = [
    'interactive' => false,
    'debug' => false,
    'access_check' => null,
    'force_access_check' => false,
    'clear_access_cache' => false,
];
foreach ($argv as $i => $arg) {
    if ($arg === '--interactive' || $arg === '-i') $options['interactive'] = true;
    if ($arg === '--debug') $options['debug'] = true;
    if ($arg === '--access-check' && isset($argv[$i+1])) $options['access_check'] = $argv[$i+1];
    if ($arg === '--force-access-check') $options['force_access_check'] = true;
    if ($arg === '--clear-access-cache') $options['clear_access_cache'] = true;
}

function find_git_dirs($base) {
    $dirs = [];
    if (!is_dir($base)) return $dirs;
    foreach (scandir($base) as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $path = "$base/$entry";
        if (is_dir("$path/.git")) {
            $dirs[$entry] = $path;
        }
    }
    return $dirs;
}

function get_package_repo_urls() {
    $lockFile = COMPOSER_LOCK;
    if (!file_exists($lockFile)) {
        echo "composer.lock not found at $lockFile\n";
        exit(1);
    }
    $lock = json_decode(file_get_contents($lockFile), true);
    $urls = [];
    foreach (['packages', 'packages-dev'] as $section) {
        if (!empty($lock[$section])) {
            foreach ($lock[$section] as $pkg) {
                $name = $pkg['name'] ?? null;
                $url = $pkg['source']['url'] ?? ($pkg['dist']['url'] ?? null);
                if ($name && $url) {
                    $cleaned = cleanRepoUrl($url);
                    if ($cleaned) {
                        $urls[$name] = $cleaned;
                    }
                }
            }
        }
    }
    return $urls;
}

function load_access_cache($path) {
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $state = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($state)) {
            return $state['repositories'] ?? [];
        }
    }
    return [];
}

function save_access_cache($path, $cache) {
    $state = [
        'version_tag' => null,
        'repositories' => $cache,
    ];
    $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json !== false) {
        $tmp = $path . '.tmp' . uniqid();
        if (file_put_contents($tmp, $json) !== false) {
            rename($tmp, $path);
        }
    }
}

function summarize_access($dirs, $repoUrls, $githubToken, $gitlabToken, $debug = false, $forceAccessCheck = false, &$accessCache = []) {
    $client = new Client();
    $writable = [];
    $readonly = [];
    $checked = 0;
    $checkedNames = [];
    $total = count($dirs);
    foreach ($dirs as $machineName => $dir) {
        // Try to find repo URL by package name
        $pkgName = null;
        foreach ($repoUrls as $name => $url) {
            if (preg_match('#/(.+)$#', $name, $m) && $m[1] === $machineName) {
                $pkgName = $name;
                break;
            }
        }
        $repoUrl = $pkgName ? $repoUrls[$pkgName] : null;
        if ($debug) {
            echo "[DEBUG] $machineName: pkgName=$pkgName, repoUrl=$repoUrl\n";
        }
        if (!$repoUrl) {
            $readonly[$machineName] = ['dir' => $dir, 'reason' => 'No repo URL found'];
            $checked++;
            $checkedNames[] = $machineName;
            if ($checked % 10 === 0) {
                echo "Checked $checked/$total: " . implode(', ', $checkedNames) . "\n";
                $checkedNames = [];
            }
            continue;
        }
        $platform = getRepoPlatform($repoUrl);
        $repoPath = extractRepoPath($repoUrl);
        $access = 'unknown';
        // Use cache if available and not forced
        if (!$forceAccessCheck && isset($accessCache[$repoUrl]['access_status']) && in_array($accessCache[$repoUrl]['access_status'], ['read-write','read-only','inaccessible','check_failed','unknown'])) {
            $access = $accessCache[$repoUrl]['access_status'];
            if ($debug) echo "[DEBUG] $machineName: access (cached) = $access\n";
        } else {
            if ($debug) {
                echo "[DEBUG] $machineName: platform=$platform, repoPath=$repoPath\n";
            }
            if ($platform === 'github') {
                $access = checkGitHubAccess($client, $repoPath, $githubToken);
            } elseif ($platform === 'gitlab') {
                $access = checkGitLabAccess($client, $repoPath, $gitlabToken);
            } else {
                $readonly[$machineName] = ['dir' => $dir, 'repo' => $repoUrl, 'reason' => 'Unknown platform'];
                $checked++;
                $checkedNames[] = $machineName;
                if ($checked % 10 === 0) {
                    echo "Checked $checked/$total: " . implode(', ', $checkedNames) . "\n";
                    $checkedNames = [];
                }
                continue;
            }
            $accessCache[$repoUrl] = $accessCache[$repoUrl] ?? [];
            $accessCache[$repoUrl]['access_status'] = $access;
        }
        if ($debug) {
            echo "[DEBUG] $machineName: access=$access\n";
        }
        if ($access === 'read-write') {
            $writable[$machineName] = ['dir' => $dir, 'repo' => $repoUrl];
        } else {
            $readonly[$machineName] = ['dir' => $dir, 'repo' => $repoUrl, 'reason' => $access];
        }
        $checked++;
        $checkedNames[] = $machineName;
        if ($checked % 10 === 0) {
            echo "Checked $checked/$total: " . implode(', ', $checkedNames) . "\n";
            $checkedNames = [];
        }
    }
    if (!empty($checkedNames)) {
        echo "Checked $checked/$total: " . implode(', ', $checkedNames) . "\n";
    }
    return [$writable, $readonly];
}

function prompt($msg) {
    echo $msg . " [Enter to continue, q to quit] ";
    $input = trim(fgets(STDIN));
    if (strtolower($input) === 'q') exit("Quitting.\n");
}

function prompt_action($msg, $actions = ['n', 'a', 's', 'q']) {
    $opts = [];
    if (in_array('a', $actions)) $opts[] = 'a=add';
    if (in_array('s', $actions)) $opts[] = 's=skip';
    if (in_array('n', $actions)) $opts[] = 'n=next';
    if (in_array('q', $actions)) $opts[] = 'q=quit';
    $optstr = '[' . implode(', ', $opts) . ']';
    echo "$msg $optstr ";
    $input = strtolower(trim(fgets(STDIN)));
    if ($input === '') $input = 'n';
    return $input;
}

function list_changed_files() {
    $output = [];
    exec('git status -s', $output);
    $files = [];
    foreach ($output as $line) {
        if (preg_match('/^[ MARC?]{2} (.+)$/', $line, $m)) {
            $files[] = $m[1];
        }
    }
    return $files;
}

function prompt_files_to_add($files) {
    echo "Select files to add (comma-separated numbers, or Enter to cancel):\n";
    foreach ($files as $i => $file) {
        echo "  " . ($i+1) . ". $file\n";
    }
    echo "> ";
    $input = trim(fgets(STDIN));
    if ($input === '') return [];
    $nums = array_map('trim', explode(',', $input));
    $selected = [];
    foreach ($nums as $n) {
        $idx = intval($n) - 1;
        if (isset($files[$idx])) $selected[] = $files[$idx];
    }
    return $selected;
}

function list_staged_files() {
    $output = [];
    exec('git diff --cached --name-only', $output);
    return $output;
}

function prompt_branch_name() {
    $default = 'upgrade-drupal11-' . date('Ymd_His');
    echo "Enter branch name [default: $default]: ";
    $input = trim(fgets(STDIN));
    return $input !== '' ? $input : $default;
}

function prompt_commit_message() {
    $default = 'Drupal 11 upgrade: staged changes';
    echo "Enter commit message [default: $default]: ";
    $input = trim(fgets(STDIN));
    return $input !== '' ? $input : $default;
}

function branch_commit_push($branch, $commitMsg) {
    // Create branch
    $branchCmd = "git checkout -b " . escapeshellarg($branch);
    $branchResult = shell_exec($branchCmd . " 2>&1");
    echo $branchResult;
    // Commit
    $commitCmd = "git commit -m " . escapeshellarg($commitMsg);
    $commitResult = shell_exec($commitCmd . " 2>&1");
    echo $commitResult;
    // Detect remote URL
    $remoteUrl = trim(shell_exec('git remote get-url origin 2>/dev/null'));
    require_once __DIR__ . '/git_access_util.php';
    $canonicalPushUrl = cleanRepoUrl($remoteUrl);
    $pushUrl = $remoteUrl;
    $rewritten = false;
    if ($canonicalPushUrl && strpos($canonicalPushUrl, 'git@') === 0) {
        $pushUrl = $canonicalPushUrl;
        $rewritten = true;
    }
    echo "Remote URL: $remoteUrl\n";
    if ($rewritten) {
        echo "Using canonical SSH push URL: $pushUrl\n";
    } else if (strpos($remoteUrl, 'git@') === 0) {
        echo "Remote is already SSH format.\n";
    } else {
        echo "[WARNING] Remote URL is not recognized for SSH rewrite. Push may fail.\n";
    }
    // Push
    $pushCmd = "git push -u " . escapeshellarg($pushUrl) . " " . escapeshellarg($branch);
    $pushResult = shell_exec($pushCmd . " 2>&1");
    echo $pushResult;
}

function analyze_dirs($dirs, $options) {
    foreach ($dirs as $machineName => $info) {
        $dir = $info['dir'];
        echo "\n==== $dir ====";
        chdir($dir);
        echo "\n--- git status (short) ---\n";
        system('git status -s');
        echo "\n--- git diff (minimal) ---\n";
        system('git diff --minimal');
        $staged = list_staged_files();
        if (!empty($staged)) {
            echo "\n--- Staged files (already added, ready to commit) ---\n";
            foreach ($staged as $f) {
                echo "  [staged] $f\n";
            }
        }
        // Always allow review if staged files exist, even if no unstaged changes
        if (!empty($options['interactive']) && (!empty($staged) || count(list_changed_files()) > 0)) {
            while (true) {
                $actions = ['a','s','n','q'];
                if (!empty($staged)) $actions[] = 'b';
                $opts = [];
                if (in_array('a', $actions)) $opts[] = 'a=add';
                if (in_array('b', $actions)) $opts[] = 'b=branch/push';
                if (in_array('s', $actions)) $opts[] = 's=skip';
                if (in_array('n', $actions)) $opts[] = 'n=next';
                if (in_array('q', $actions)) $opts[] = 'q=quit';
                $optstr = '[' . implode(', ', $opts) . ']';
                echo "\nAction? $optstr ";
                $input = strtolower(trim(fgets(STDIN)));
                if ($input === '') $input = 'n';
                if ($input === 'a') {
                    $files = list_changed_files();
                    if (empty($files)) {
                        echo "No changed files to add.\n";
                        continue;
                    }
                    $to_add = prompt_files_to_add($files);
                    if (!empty($to_add)) {
                        foreach ($to_add as $f) {
                            echo "Adding $f\n";
                            system('git add ' . escapeshellarg($f));
                        }
                        echo "Files added.\n";
                    } else {
                        echo "No files selected.\n";
                    }
                } elseif ($input === 'b' && !empty($staged)) {
                    $branch = prompt_branch_name();
                    $commitMsg = prompt_commit_message();
                    branch_commit_push($branch, $commitMsg);
                    // After push, offer to continue or quit
                    echo "\nBranch pushed. Continue reviewing this package? [y/N]: ";
                    $cont = strtolower(trim(fgets(STDIN)));
                    if ($cont !== 'y') break;
                } elseif ($input === 's') {
                    echo "Skipped $machineName\n";
                    break;
                } elseif ($input === 'q') {
                    exit("Quitting.\n");
                } else { // 'n' or Enter
                    break;
                }
                // Refresh staged files for next loop
                $staged = list_staged_files();
            }
        }
        chdir(__DIR__);
    }
}

function main($options) {
    list($githubToken, $gitlabToken) = loadGitTokens();
    if ($options['access_check']) {
        $url = $options['access_check'];
        $client = new \GuzzleHttp\Client();
        $cleaned = cleanRepoUrl($url);
        $platform = getRepoPlatform($cleaned);
        $repoPath = extractRepoPath($cleaned);
        echo "\n[ACCESS CHECK] URL: $url\n";
        echo "Platform: $platform\nRepo path: $repoPath\n";
        if ($platform === 'github') {
            $access = checkGitHubAccess($client, $repoPath, $githubToken);
            echo "GitHub access: $access\n";
        } elseif ($platform === 'gitlab') {
            $access = checkGitLabAccess($client, $repoPath, $gitlabToken);
            echo "GitLab access: $access\n";
        } else {
            echo "Unknown platform or unsupported URL.\n";
        }
        return;
    }
    if ($options['clear_access_cache']) {
        if (file_exists(ACCESS_CACHE_FILE)) {
            unlink(ACCESS_CACHE_FILE);
            echo "Access cache cleared.\n";
        }
    }
    echo "GITHUB_TOKEN: " . substr($githubToken, 0, 8) . "...\n";
    echo "GITLAB_DO_TOKEN: " . substr($gitlabToken, 0, 8) . "...\n";
    if ($options['debug']) {
        echo "[DEBUG] GITHUB_TOKEN loaded: " . (!empty($githubToken) ? 'yes' : 'no') . "\n";
        echo "[DEBUG] GITLAB_DO_TOKEN loaded: " . (!empty($gitlabToken) ? 'yes' : 'no') . "\n";
    }
    $moduleDirs = find_git_dirs(MODULES_CONTRIB);
    $profileDirs = find_git_dirs(PROFILES_CONTRIB);
    $allDirs = $moduleDirs + $profileDirs;
    $repoUrls = get_package_repo_urls();
    $accessCache = load_access_cache(ACCESS_CACHE_FILE);
    echo "Checking repository access for all packages...\n";
    list($writable, $readonly) = summarize_access($allDirs, $repoUrls, $githubToken, $gitlabToken, !empty($options['debug']), !empty($options['force_access_check']), $accessCache);
    save_access_cache(ACCESS_CACHE_FILE, $accessCache);
    echo "\nWritable packages (read-write access):\n";
    foreach ($writable as $name => $info) {
        echo "  - $name ({$info['repo']})\n";
    }
    echo "\nRead-only or unknown packages:\n";
    foreach ($readonly as $name => $info) {
        $reason = $info['reason'] ?? 'unknown';
        echo "  - $name ($reason)\n";
    }
    if (empty($writable)) {
        echo "\nNo writable packages found. Exiting.\n";
        return;
    }
    echo "\nProceeding with writable packages...\n";
    analyze_dirs($writable, $options);
    echo "\nAll done.\n";

    $client = new \GuzzleHttp\Client();
    $repoPath = 'YCloudYUSA/y_lb'; // for GitHub
    $access = checkGitHubAccess($client, $repoPath, $githubToken);
    echo "GitHub access for $repoPath: $access\n";

    $repoPath = 'project/y_lb_article'; // for Drupal.org GitLab
    $access = checkGitLabAccess($client, $repoPath, $gitlabToken);
    echo "GitLab access for $repoPath: $access\n";
}

main($options); 