<?php

/**
 * Script to check repository write access using GitHub and GitLab APIs
 * and update a state file. Includes optional size check for specific repos.
 *
 * Usage: php git_access_check_script_v1.php [--composer-dir=distribution] [--force-access-check] [--show-problematic]
 *
 * Executes `composer show` to get repository URLs, determines the platform,
 * calls the respective API to check user permissions (skipping known 'read-write' ones unless forced),
 * attempts to get latest release zip size for specific hardcoded repos,
 * and updates the access_status in docs_generation_state.json.
 * Outputs a summary broken down by platform. Optionally lists problematic repos.
 *
 * Requires Composer dependencies:
 * - composer require guzzlehttp/guzzle vlucas/phpdotenv symfony/process
 * Requires a .env file in the script directory (or parent) with:
 * - GITHUB_TOKEN="your_github_pat"
 * - GITLAB_DO_TOKEN="your_drupal_gitlab_pat"
 * Arguments:
 * --composer-dir=path : Path to the directory containing the project's composer.json/lock (default: distribution)
 * --force-access-check: Re-check API access for ALL repos, even those previously marked 'read-write'.
 * --show-problematic  : List repositories marked as inaccessible, check_failed, or unknown_platform at the end.
 */

// Ensure vendor autoload exists
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    die("Error: vendor/autoload.php not found. Please run 'composer install' in the script directory.\n");
}

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException; // Specifically for 4xx errors
use GuzzleHttp\Promise; // For concurrent requests if needed later
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


// --- Load Environment Variables ---
try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    logMsg("Loaded environment variables from .env file.");
} catch (\Dotenv\Exception\InvalidPathException $e) {
    logMsg("Warning: Could not find .env file. Relying on system environment variables.");
} catch (\Exception $e) {
    logMsg("Warning: Error loading .env file: " . $e->getMessage());
}


// --- Configuration ---

// Path to the state file
$stateFilePath = 'docs_generation_state.json';

// Directory where `composer show` should be run
$composerWorkingDir = 'distribution';

// API Tokens from Environment Variables
$githubToken = $_ENV['GITHUB_TOKEN'] ?? getenv('GITHUB_TOKEN');
$gitlabToken = $_ENV['GITLAB_DO_TOKEN'] ?? getenv('GITLAB_DO_TOKEN');

// Repositories to check size for (hardcoded for now)
$reposToCheckSize = [
    'https://github.com/YCloudYUSA/y_lb.git',
    'https://git.drupalcode.org/project/ws_small_y.git',
];


// --- Parse Arguments ---
$short_opts = "";
$long_opts = [
    "composer-dir::", "force-access-check", "show-problematic",
];
$options = getopt($short_opts, $long_opts);

if (isset($options['composer-dir'])) {
    if ($options['composer-dir'] === false) { die("Error: --composer-dir option requires a value.\n"); }
    $composerWorkingDir = $options['composer-dir'];
    logMsg("Using Composer working directory: " . $composerWorkingDir);
}
$forceAccessCheck = isset($options['force-access-check']);
if ($forceAccessCheck) { logMsg("Option --force-access-check enabled: Re-checking all repository access statuses."); }
$showProblematic = isset($options['show-problematic']);
if ($showProblematic) { logMsg("Option --show-problematic enabled: Will list problematic repos at the end."); }

$absoluteComposerWorkingDir = realpath($composerWorkingDir);
if ($absoluteComposerWorkingDir === false || !is_dir($absoluteComposerWorkingDir)) {
     die("Error: Composer working directory '{$composerWorkingDir}' not found or is not accessible.\n");
}
logMsg("Absolute composer working directory: " . $absoluteComposerWorkingDir);


// --- Validate Configuration ---
if (empty($githubToken)) { die("Error: GITHUB_TOKEN environment variable not set.\n"); }
if (empty($gitlabToken)) { die("Error: GITLAB_DO_TOKEN environment variable not set.\n"); }

// --- Helper Functions ---

function logMsg(string $message): void {
    $message = mb_convert_encoding($message, 'UTF-8', 'UTF-8');
    echo date('[Y-m-d H:i:s] ') . $message . PHP_EOL;
}

/**
 * Cleans and standardizes a Git repository URL.
 */
function cleanRepoUrl(string $rawUrl): ?string {
    $url = trim($rawUrl);
    if (str_contains($url, 'drupal.org/project/')) { return null; }
    if (!str_starts_with($url, 'http') && !str_starts_with($url, 'git@')) { return null; }
    if (str_contains($url, 'cgit.drupalcode.org/')) {
        $url = str_replace('http://cgit.drupalcode.org/', 'https://git.drupalcode.org/', $url);
        if (!str_ends_with($url, '.git')) { $url .= '.git'; }
    }
    if (preg_match('/^(https?:\/\/|git@)(github\.com|git\.drupalcode\.org)[:\/](.*?)(\/-\/tree\/|\/tree\/).*/', $url, $matches)) {
         $protocol = $matches[1]; $host = $matches[2]; $path = $matches[3];
         $cleanedUrl = rtrim($protocol . $host . ($protocol === 'git@' ? ':' : '/') . $path, '/');
         if (!str_ends_with($cleanedUrl, '.git')) { $cleanedUrl .= '.git'; }
         $url = $cleanedUrl;
    }
    if ((str_contains($url, 'github.com') || str_contains($url, 'git.drupalcode.org')) && !str_ends_with($url, '.git')) {
        if (substr_count(ltrim(parse_url($url, PHP_URL_PATH) ?: '', '/'), '/') >= 1 || str_contains($url, ':')) { $url .= '.git'; }
        else { return null; }
    }
    if ((str_contains($url, 'github.com') || str_contains($url, 'git.drupalcode.org')) && preg_match('/^(https?:\/\/|git@).*\.git$/', $url)) { return $url; }
    return null;
}

/**
 * Gets repository URLs by running `composer show` and parsing the JSON output.
 */
function getUrlsFromComposerShow(string $workingDir): array {
    logMsg("Getting repository URLs via `composer show` in directory: $workingDir");
    $composerExecutable = __DIR__ . '/vendor/bin/composer';
    if (!is_executable($composerExecutable)) { $composerExecutable = 'composer'; logMsg("Local composer not found or not executable, trying global 'composer'"); }
    else { logMsg("Using local composer executable: $composerExecutable"); }
    $command = [$composerExecutable, 'show', '--installed', '--format=json'];
    $process = new Process($command, $workingDir);
    $jsonOutput = null; $errorOutput = null; $exitCode = null;
    try {
        logMsg("Executing command: " . $process->getCommandLine());
        $process->setTimeout(120); $exitCode = $process->run();
        $jsonOutput = $process->getOutput(); $errorOutput = $process->getErrorOutput();
        logMsg("Command exit code: " . ($exitCode ?? 'N/A'));
        if (!empty($errorOutput)) { logMsg("Command stderr:\n" . preg_replace('/[[:^print:]]/', '?', $errorOutput)); }
        if ($exitCode !== 0) { logMsg("Error: `composer show` command failed with exit code $exitCode."); return []; }
        logMsg("Command stdout length: " . strlen($jsonOutput));
    } catch (\Exception $e) { logMsg("Error executing `composer show`: " . $e->getMessage()); return []; }
    $data = json_decode($jsonOutput, true);
    if (json_last_error() !== JSON_ERROR_NONE) { logMsg("Error: Failed to decode JSON from `composer show`: " . json_last_error_msg()); return []; }
    $rawUrls = []; $packageCount = 0;
    if (isset($data['installed']) && is_array($data['installed'])) {
        logMsg("Processing 'installed' array...");
        foreach ($data['installed'] as $package) {
            $packageCount++; $packageName = $package['name'] ?? 'N/A'; $urlToAdd = null;
            if (isset($package['source'])) {
                if (is_string($package['source']) && !empty($package['source'])) { $urlToAdd = $package['source']; }
                elseif (is_array($package['source']) && isset($package['source']['url']) && is_string($package['source']['url']) && !empty($package['source']['url'])) { $urlToAdd = $package['source']['url']; }
            }
            if ($urlToAdd === null && isset($package['dist']['url']) && isset($package['dist']['type']) && $package['dist']['type'] === 'git' && is_string($package['dist']['url']) && !empty($package['dist']['url'])) { $urlToAdd = $package['dist']['url']; }
            if ($urlToAdd !== null) { $rawUrls[] = $urlToAdd; }
        }
        logMsg("Processed $packageCount packages.");
    } else { logMsg("Error: Could not find 'installed' array in composer show output."); return []; }
    logMsg("Found " . count($rawUrls) . " raw source/dist URLs before cleaning.");
    $cleanedUrls = [];
    foreach ($rawUrls as $rawUrl) { $cleaned = cleanRepoUrl($rawUrl); if ($cleaned !== null) { $cleanedUrls[] = $cleaned; } }
    $uniqueUrls = array_unique($cleanedUrls);
    logMsg("Found " . count($uniqueUrls) . " unique and cleaned git URLs.");
    return $uniqueUrls;
}

/**
 * Loads the state file or initializes a new state structure.
 */
function loadState(string $path): array {
    if (file_exists($path)) {
        $content = file_get_contents($path); $state = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($state)) { $state['repositories'] = $state['repositories'] ?? []; return $state; }
        else { logMsg("Warning: State file $path is invalid JSON or not an array. Initializing new state."); }
    } else { logMsg("State file not found. Initializing new state."); }
    return ['version_tag' => null, 'repositories' => []];
}

/**
 * Saves the state data to the JSON file.
 */
function saveState(string $path, array $state): bool {
    // logMsg("Saving state file: $path"); // Reduce noise
    try {
        if (isset($state['repositories'])) { ksort($state['repositories']); }
        $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) { throw new \JsonException(json_last_error_msg()); }
        $tempPath = $path . '.tmp' . uniqid();
        if (file_put_contents($tempPath, $json) === false) { throw new \RuntimeException("Failed to write temporary state file to $tempPath"); }
        if (!rename($tempPath, $path)) { throw new \RuntimeException("Failed to rename temporary state file to $path"); }
        return true;
    } catch (\Exception $e) { logMsg("[CRITICAL] Failed to save state file '$path': " . $e->getMessage()); if (isset($tempPath) && file_exists($tempPath)) { @unlink($tempPath); } return false; }
}

/**
 * Extracts owner/repo or project path from Git URL.
 */
function extractRepoPath(string $url): ?string {
    $path = null;
    if (str_starts_with($url, 'git@')) { $path = preg_replace('/^git@.*?:/', '', $url); }
    elseif (str_starts_with($url, 'https://')) { $parsed = parse_url($url); if (isset($parsed['path'])) { $path = ltrim($parsed['path'], '/'); } }
    return $path ? preg_replace('/\.git$/', '', $path) : null;
}

/**
 * Checks write access for a GitHub repository using the API.
 */
function checkGitHubAccess(Client $httpClient, string $repoPath, string $githubToken): string {
    $apiUrl = "https://api.github.com/repos/{$repoPath}";
    try {
        // logMsg("Checking GitHub repo: $repoPath");
        $response = $httpClient->request('GET', $apiUrl, [
            'headers' => ['Authorization' => "Bearer {$githubToken}", 'Accept' => 'application/vnd.github.v3+json', 'X-GitHub-Api-Version' => '2022-11-28'],
            'http_errors' => true, 'connect_timeout' => 5, 'timeout' => 15
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        if (isset($data['permissions'])) {
            if (!empty($data['permissions']['push']) || !empty($data['permissions']['admin'])) { return 'read-write'; } else { return 'read-only'; }
        } else { logMsg("Warning: Could not determine permissions for $repoPath from API response. Assuming read-only."); return 'read-only'; }
    } catch (ClientException $e) {
        $statusCode = $e->getResponse()->getStatusCode();
        if ($statusCode === 404) { return 'inaccessible'; }
        elseif ($statusCode === 401 || $statusCode === 403) { return 'read-only'; }
        else { logMsg("Error checking GitHub access for $repoPath: HTTP {$statusCode} - " . $e->getMessage()); return 'check_failed'; }
    } catch (RequestException $e) { logMsg("Error checking GitHub access for $repoPath: " . $e->getMessage()); return 'check_failed';
    } catch (\Exception $e) { logMsg("Unexpected error checking GitHub access for $repoPath: " . $e->getMessage()); return 'check_failed'; }
}

/**
 * Checks write access for a Drupal.org GitLab repository using the API.
 */
function checkGitLabAccess(Client $httpClient, string $repoPath, string $gitlabToken): string {
    $projectId = urlencode($repoPath);
    $apiUrl = "https://git.drupalcode.org/api/v4/projects/{$projectId}";
    try {
        // logMsg("Checking Drupal GitLab repo: $repoPath (ID: $projectId)");
        $response = $httpClient->request('GET', $apiUrl, [
            'headers' => ['PRIVATE-TOKEN' => $gitlabToken, 'Accept' => 'application/json'],
            'http_errors' => true, 'connect_timeout' => 5, 'timeout' => 15
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        $projectAccessLevel = $data['permissions']['project_access']['access_level'] ?? 0;
        $groupAccessLevel = $data['permissions']['group_access']['access_level'] ?? 0;
        $effectiveAccessLevel = max($projectAccessLevel, $groupAccessLevel);
        if ($effectiveAccessLevel >= 30) { return 'read-write'; } else { return 'read-only'; }
    } catch (ClientException $e) {
        $statusCode = $e->getResponse()->getStatusCode();
        if ($statusCode === 404) { return 'inaccessible'; }
        elseif ($statusCode === 401 || $statusCode === 403) { return 'read-only'; }
        else { logMsg("Error checking GitLab access for $repoPath: HTTP {$statusCode} - " . $e->getMessage()); return 'check_failed'; }
    } catch (RequestException $e) { logMsg("Error checking GitLab access for $repoPath: " . $e->getMessage()); return 'check_failed';
    } catch (\Exception $e) { logMsg("Unexpected error checking GitLab access for $repoPath: " . $e->getMessage()); return 'check_failed'; }
}

/**
 * Attempts to get the size of the latest release/tag zip archive.
 *
 * @param Client $httpClient Guzzle client instance.
 * @param string $platform 'github' or 'gitlab'.
 * @param string $repoPath Owner/repo or group/project path.
 * @param string $token API token for the platform.
 * @return ?int Size in bytes, or null if not found/error.
 */
function getReleaseZipSize(Client $httpClient, string $platform, string $repoPath, string $token): ?int {
    $zipUrl = null;
    $headers = [];

    logMsg("Attempting to get latest release size for $platform repo: $repoPath");

    try {
        if ($platform === 'github') {
            $apiUrl = "https://api.github.com/repos/{$repoPath}/releases/latest";
            $headers = ['Authorization' => "Bearer {$token}", 'Accept' => 'application/vnd.github.v3+json', 'X-GitHub-Api-Version' => '2022-11-28'];
            $response = $httpClient->request('GET', $apiUrl, ['headers' => $headers, 'http_errors' => true, 'timeout' => 10]);
            $data = json_decode($response->getBody()->getContents(), true);
            $zipUrl = $data['zipball_url'] ?? null;
            if ($zipUrl) {
                logMsg("Found GitHub latest release zip URL: $zipUrl");
            } else {
                logMsg("Could not find zipball_url in latest GitHub release API response for $repoPath.");
                return null;
            }
        } elseif ($platform === 'gitlab') {
            $projectId = urlencode($repoPath);
            // Get the latest tag based on commit date (often correlates with release)
            $apiUrl = "https://git.drupalcode.org/api/v4/projects/{$projectId}/repository/tags?order_by=updated&sort=desc";
            $headers = ['PRIVATE-TOKEN' => $token, 'Accept' => 'application/json'];
            $response = $httpClient->request('GET', $apiUrl, ['headers' => $headers, 'http_errors' => true, 'timeout' => 10]);
            $tags = json_decode($response->getBody()->getContents(), true);
            if (!empty($tags) && isset($tags[0]['name'])) {
                $latestTagName = $tags[0]['name'];
                $projectName = basename($repoPath);
                $zipUrl = "https://git.drupalcode.org/{$repoPath}/-/archive/{$latestTagName}/{$projectName}-{$latestTagName}.zip";
                logMsg("Found GitLab latest tag '$latestTagName', constructed zip URL: $zipUrl");
            } else {
                logMsg("Could not find any tags via GitLab API for $repoPath.");
                return null;
            }
        } else {
            return null; // Unsupported platform
        }

        // Now make the HEAD request to get Content-Length
        if ($zipUrl) {
            $headHeaders = ($platform === 'github') ? ['Authorization' => "Bearer {$token}"] : [];
            $headResponse = $httpClient->request('HEAD', $zipUrl, [
                'headers' => $headHeaders,
                'http_errors' => true,
                'allow_redirects' => ['max' => 5],
                'timeout' => 10,
            ]);

            $contentLength = $headResponse->getHeaderLine('Content-Length');
            if ($contentLength && is_numeric($contentLength)) {
                logMsg("Found Content-Length: $contentLength bytes");
                return (int)$contentLength;
            } else {
                logMsg("Content-Length header not found or invalid in HEAD response for $zipUrl.");
            }
        }
    } catch (ClientException $e) {
        $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
        if ($statusCode === 404) { logMsg("Could not find latest release/tag or archive URL for $repoPath (API/HEAD returned 404)."); }
        else { logMsg("API/HEAD Error getting release/tag info for $repoPath: HTTP {$statusCode} - " . $e->getMessage()); }
    } catch (RequestException $e) { logMsg("Network Error getting release/tag info or HEAD request for $repoPath: " . $e->getMessage());
    } catch (\Exception $e) { logMsg("Unexpected error getting release size for $repoPath: " . $e->getMessage()); }

    return null; // Return null if size couldn't be determined
}

// --- Main Execution ---

logMsg("Starting Git Repository Access Check Script (API Method, Composer Show)...");

$httpClient = new Client(['timeout' => 15.0, 'connect_timeout' => 5.0]);
$state = loadState($stateFilePath);
$repoUrls = getUrlsFromComposerShow($absoluteComposerWorkingDir);

if (empty($repoUrls)) { logMsg("No valid Git URLs extracted from `composer show`. Exiting."); saveState($stateFilePath, $state); exit(1); }

// Initialize counters and problematic list
$counters = [
    'github' => ['read-write' => 0, 'read-only' => 0, 'inaccessible' => 0, 'check_failed' => 0, 'unknown' => 0, 'skipped' => 0],
    'gitlab' => ['read-write' => 0, 'read-only' => 0, 'inaccessible' => 0, 'check_failed' => 0, 'unknown' => 0, 'skipped' => 0],
    'other'  => ['read-write' => 0, 'read-only' => 0, 'inaccessible' => 0, 'check_failed' => 0, 'unknown' => 0, 'skipped' => 0],
];
$problematicRepos = ['inaccessible' => [], 'check_failed' => [], 'unknown' => []];
$updatedCount = 0; $newCount = 0;

foreach ($repoUrls as $url) {
    $accessStatus = 'unknown'; $platform = 'other'; $performCheck = true;
    $currentState = $state['repositories'][$url] ?? null;
    $currentAccessStatus = $currentState['access_status'] ?? 'unknown';

    if (str_contains($url, 'github.com')) { $platform = 'github'; }
    elseif (str_contains($url, 'git.drupalcode.org')) { $platform = 'gitlab'; }

    if (!$forceAccessCheck && $currentState && $currentAccessStatus === 'read-write') {
        $accessStatus = $currentAccessStatus; $performCheck = false; $counters[$platform]['skipped']++;
    }

    if ($performCheck) {
        $repoPath = extractRepoPath($url);
        if (!$repoPath) {
            logMsg("Warning: Could not extract repository path from URL: $url. Setting status to check_failed.");
            $accessStatus = 'check_failed'; $platform = 'other';
        } else {
            if ($platform === 'github') { $accessStatus = checkGitHubAccess($httpClient, $repoPath, $githubToken); }
            elseif ($platform === 'gitlab') { $accessStatus = checkGitLabAccess($httpClient, $repoPath, $gitlabToken); }
            else { logMsg("Warning: Unsupported Git platform for URL: $url. Setting status to unknown."); $accessStatus = 'unknown'; }
        }
        usleep(50000); // Delay only if API check was performed
    }

    // --- Check Size for Specific Repos ---
    if (in_array($url, $reposToCheckSize) && $platform !== 'other') {
         $token = ($platform === 'github') ? $githubToken : $gitlabToken;
         $repoPathForSize = extractRepoPath($url);
         if ($repoPathForSize) {
             $size = getReleaseZipSize($httpClient, $platform, $repoPathForSize, $token);
             if ($size !== null) {
                 logMsg(">>> Latest release/tag archive size for $repoPathForSize: " . round($size / 1024 / 1024, 2) . " MB");
             } else {
                 logMsg(">>> Could not determine latest release/tag archive size for $repoPathForSize.");
             }
             usleep(50000); // Add small delay after potential extra API calls
         }
    }
    // --- End Check Size ---

    // Update state file entry
    if (!$currentState) {
        $state['repositories'][$url] = [
            'access_status' => $accessStatus, 'status' => 'pending', 'last_run' => null,
            'last_error' => null, 'generated_files' => [], 'branch_name' => null,
        ];
        $newCount++;
    } elseif ($currentAccessStatus !== $accessStatus || !isset($currentState['status'])) {
        $state['repositories'][$url]['access_status'] = $accessStatus;
        $state['repositories'][$url]['status'] = $currentState['status'] ?? 'pending';
        if ($currentAccessStatus !== $accessStatus) { $updatedCount++; logMsg("Updated access status for $url from '$currentAccessStatus' to '$accessStatus'"); }
    }

    $counters[$platform][$accessStatus]++;
    if (in_array($accessStatus, ['inaccessible', 'check_failed', 'unknown'])) { $problematicRepos[$accessStatus][$url] = $platform; }

} // End foreach repoUrl

if (!saveState($stateFilePath, $state)) { logMsg("Critical Error: Failed to save updated state file!"); exit(1); }
logMsg("State file update process complete. Added: $newCount, Updated: $updatedCount");

// --- Platform Breakdown Summary Output ---
logMsg("Access check complete. Summary for this run:");
$platforms = ['github', 'gitlab', 'other'];
$statuses = ['read-write', 'read-only', 'inaccessible', 'check_failed', 'unknown', 'skipped'];
$statusLabels = [
    'read-write' => 'Writable', 'read-only' => 'Read-Only', 'inaccessible' => 'Inaccessible',
    'check_failed' => 'Check Failed', 'unknown' => 'Unknown Platform', 'skipped' => 'Skipped (Already Read-Write)',
];
$platformLabels = ['github' => 'GitHub', 'gitlab' => 'GitLab(d.o)', 'other' => 'Other/Unknown'];

foreach ($statuses as $status) {
    $statusLabel = $statusLabels[$status]; $hasOutputForStatus = false;
    foreach ($platforms as $p) {
        $count = $counters[$p][$status];
        if ($count > 0) { logMsg(sprintf(" - %-30s: %-12s: %d", $statusLabel, $platformLabels[$p], $count)); $hasOutputForStatus = true; }
    }
     if ($status === 'skipped' && !$hasOutputForStatus) { logMsg(sprintf(" - %-30s: %-12s: 0", $statusLabel, 'Total')); }
}
// --- End Platform Breakdown Summary Output ---

// --- Optional Problematic Repo List ---
if ($showProblematic) {
    $hasProblematic = false;
    foreach ($problematicRepos as $status => $urls) { if (!empty($urls)) { $hasProblematic = true; break; } }
    if ($hasProblematic) {
        logMsg("--- Problematic Repositories ---");
        foreach ($problematicRepos as $status => $urls) {
            if (!empty($urls)) {
                logMsg("Repositories with status '$status':"); ksort($urls);
                foreach ($urls as $probUrl => $probPlatform) { logMsg(sprintf("  - [%s] %s", $platformLabels[$probPlatform], $probUrl)); }
            }
        }
        logMsg("--- End Problematic Repositories ---");
    } else { logMsg("No problematic repositories found in this run."); }
}
// --- End Optional Problematic Repo List ---

logMsg("Script finished.");
exit(empty($problematicRepos['check_failed']) && empty($problematicRepos['unknown']) ? 0 : 1); // Exit 1 if checks failed or platform unknown

?>
