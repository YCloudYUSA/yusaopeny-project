#!/usr/bin/env php
<?php
/**
 * Script to clone Drupal modules/profiles and copy their .git folders for diffing.
 * Usage: php scripts/clone_git_for_diff.php [--debug] [--dry-run] [--yes|-y]
 */

const REPOS_DIR = __DIR__ . '/../repos';
const DOCROOT = __DIR__ . '/../docroot';
const MODULES_CONTRIB = DOCROOT . '/modules/contrib';
const PROFILES_CONTRIB = DOCROOT . '/profiles/contrib';

$options = [
    'debug' => false,
    'dry_run' => false,
    'yes' => false,
];
foreach ($argv as $arg) {
    if ($arg === '--debug') $options['debug'] = true;
    if ($arg === '--dry-run') $options['dry_run'] = true;
    if ($arg === '--yes' || $arg === '-y') $options['yes'] = true;
}

function icon($type) {
    return [
        'ok' => '✅',
        'warn' => '⚠️',
        'info' => '➡️',
        'skip' => '⏭️',
        'run' => '⏩',
        'fail' => '🛑',
        'ask' => '❓',
        'dir' => '📁',
        'git' => '🌱',
        'copy' => '📋',
        'debug' => '🐞',
    ][$type] ?? '';
}

function debug($msg, $options) {
    if (!empty($options['debug'])) {
        echo icon('debug') . " [DEBUG] $msg\n";
    }
}

function confirm($prompt, $default = 'y', $options = []) {
    if (!empty($options['yes'])) {
        echo icon('ask') . " $prompt [y/n/s/c/a] y (auto)\n";
        return 'y';
    }
    $opts = '[y/n/s/c/a]';
    $default = strtolower($default);
    $map = ['y' => 'yes', 'n' => 'no', 's' => 'skip', 'c' => 'cancel', 'a' => 'all'];
    $help = "\nOptions:\n" .
            "  y (yes)    - Proceed with this action\n" .
            "  n (no)     - Skip this action\n" .
            "  s (skip)   - Skip this action and continue with next\n" .
            "  c (cancel) - Cancel the entire process\n" .
            "  a (all)    - Proceed with this and all remaining actions\n";
    
    while (true) {
        // Clear line and show full context
        echo "\n" . icon('ask') . " $prompt\n";
        echo $help;
        echo "\nEnter your choice $opts: ";
        $input = strtolower(trim(fgets(STDIN)));
        if ($input === '' && $default) {
            $input = $default;
        }
        if (isset($map[$input])) {
            return $input;
        }
        echo "Invalid option. ";
    }
}

function read_json($file) {
    if (!file_exists($file)) {
        fwrite(STDERR, icon('fail') . " File not found: $file\n");
        exit(1);
    }
    $json = file_get_contents($file);
    return json_decode($json, true);
}

function ensure_dir($dir, $options) {
    if (!is_dir($dir)) {
        $resp = confirm("Directory '$dir' does not exist. Create it?", 'y', $options);
        if ($resp === 'c') exit(icon('fail') . " Cancelled.\n");
        if ($resp === 's' || $resp === 'n') return false;
        if (!empty($options['dry_run'])) {
            echo icon('dir') . " [DRY-RUN] Would create directory: $dir\n";
        } else {
            mkdir($dir, 0777, true);
            echo icon('dir') . " Created directory: $dir\n";
        }
        return true;
    }
    return true;
}

function get_drupal_packages($composer_lock) {
    $pkgs = [];
    foreach (['packages', 'packages-dev'] as $section) {
        if (!empty($composer_lock[$section])) {
            foreach ($composer_lock[$section] as $pkg) {
                if (isset($pkg['type']) && in_array($pkg['type'], ['drupal-module', 'drupal-profile'])) {
                    $pkgs[] = $pkg;
                }
            }
        }
    }
    return $pkgs;
}

function get_install_path($pkg) {
    $name = $pkg['name'];
    $base = ($pkg['type'] === 'drupal-module') ? MODULES_CONTRIB : PROFILES_CONTRIB;
    $parts = explode('/', $name);
    $machine_name = end($parts);
    return "$base/$machine_name";
}

function get_repo_url($pkg) {
    if (!empty($pkg['source']['url'])) {
        return $pkg['source']['url'];
    }
    if (!empty($pkg['dist']['url'])) {
        return $pkg['dist']['url'];
    }
    return null;
}

function get_repo_ref($pkg) {
    if (!empty($pkg['source']['reference'])) {
        return $pkg['source']['reference'];
    }
    if (!empty($pkg['dist']['reference'])) {
        return $pkg['dist']['reference'];
    }
    return null;
}

function copy_git_folder($from, $to, $options) {
    $src = rtrim($from, '/') . '/.git';
    $dst = rtrim($to, '/') . '/.git';
    if (!is_dir($src)) {
        echo icon('warn') . " No .git found in $from\n";
        return false;
    }
    $cmd = sprintf('rsync -a "%s" "%s"', $src, $to);
    if (is_dir($dst)) {
        $resp = confirm(".git already exists in $to. Overwrite? Command: $cmd", 'n', $options);
        if ($resp === 'c') exit(icon('fail') . " Cancelled.\n");
        if ($resp === 's' || $resp === 'n') return false;
        if (!empty($options['dry_run'])) {
            echo icon('copy') . " [DRY-RUN] Would remove existing .git in $to\n";
        } else {
            system(sprintf('rm -rf "%s"', $dst));
        }
    } else {
        $resp = confirm("Copy .git from $src to $dst? Command: $cmd", 'y', $options);
        if ($resp === 'c') exit(icon('fail') . " Cancelled.\n");
        if ($resp === 's' || $resp === 'n') return false;
    }
    if (!empty($options['dry_run'])) {
        echo icon('copy') . " [DRY-RUN] Would run: $cmd\n";
        return true;
    }
    system($cmd, $retval);
    if ($retval === 0) {
        echo icon('ok') . " .git copied to $to\n";
        return true;
    } else {
        echo icon('fail') . " Failed to copy .git to $to\n";
        return false;
    }
}

function main($options) {
    $composer_lock = read_json(__DIR__ . '/../composer.lock');
    if (!ensure_dir(REPOS_DIR, $options)) return;
    $pkgs = get_drupal_packages($composer_lock);
    $apply_all = false;
    foreach ($pkgs as $pkg) {
        $name = $pkg['name'];
        $type = $pkg['type'];
        $repo_url = get_repo_url($pkg);
        $repo_ref = get_repo_ref($pkg);
        $install_path = get_install_path($pkg);
        $parts = explode('/', $name);
        $repo_dir = REPOS_DIR . '/' . end($parts);
        echo "\n" . icon('info') . " Processing $name ($type)\n";
        debug("repo_url: $repo_url, repo_ref: $repo_ref, install_path: $install_path, repo_dir: $repo_dir", $options);
        if (!$repo_url) {
            echo icon('warn') . " No repo URL found, skipping\n";
            continue;
        }
        // Confirm before git clone
        $clone_cmd = sprintf('git clone "%s" "%s"', $repo_url, $repo_dir);
        if (!is_dir($repo_dir . '/.git')) {
            if (!$apply_all) {
                $resp = confirm("Do you want to clone the repository for $name?\nCommand that will be executed: $clone_cmd", 'y', $options);
                if ($resp === 'c') exit(icon('fail') . " Cancelled.\n");
                if ($resp === 's' || $resp === 'n') {
                    echo icon('skip') . " Skipped cloning $repo_url\n";
                    continue;
                }
                if ($resp === 'a') {
                    $apply_all = true;
                }
            }
            if (!empty($options['dry_run'])) {
                echo icon('git') . " [DRY-RUN] Would run: $clone_cmd\n";
            } else {
                echo icon('git') . " Cloning $repo_url ...\n";
                system($clone_cmd, $retval);
                if ($retval !== 0) {
                    echo icon('fail') . " Clone failed, skipping\n";
                    continue;
                }
            }
        } else {
            echo icon('ok') . " Repo already cloned\n";
        }
        // Checkout correct ref if possible
        if ($repo_ref) {
            chdir($repo_dir);
            $cmd = sprintf('git checkout %s', escapeshellarg($repo_ref));
            if (!$apply_all) {
                $resp = confirm("Do you want to checkout reference $repo_ref for $name?\nCommand that will be executed: $cmd", 'y', $options);
                if ($resp === 'c') exit(icon('fail') . " Cancelled.\n");
                if ($resp === 's' || $resp === 'n') {
                    echo icon('skip') . " Skipped checkout for $name\n";
                    chdir(__DIR__);
                    continue;
                }
                if ($resp === 'a') {
                    $apply_all = true;
                }
            }
            if (!empty($options['dry_run'])) {
                echo icon('git') . " [DRY-RUN] Would run: $cmd\n";
            } else {
                echo icon('git') . " Checking out $repo_ref ...\n";
                system($cmd);
            }
            chdir(__DIR__);
        }
        // Copy .git folder (with confirmation inside)
        if (!$apply_all) {
            $resp = confirm("Do you want to copy .git folder for $name to $install_path?", 'y', $options);
            if ($resp === 'c') exit(icon('fail') . " Cancelled.\n");
            if ($resp === 's' || $resp === 'n') {
                echo icon('skip') . " Skipped .git copy for $name\n";
                continue;
            }
            if ($resp === 'a') {
                $apply_all = true;
            }
        }
        copy_git_folder($repo_dir, $install_path, $options);
    }
    echo "\n" . icon('ok') . " All done. You can now run 'git diff' in module/profile folders.\n";
}

main($options); 