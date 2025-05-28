<?php
// Utility functions for checking git repository access (GitHub, GitLab/Drupal.org)
// Requires: guzzlehttp/guzzle, vlucas/phpdotenv

use GuzzleHttp\Client;
use Dotenv\Dotenv;

/**
 * Extracts owner/repo or project path from Git URL.
 */
function extractRepoPath(string $url): ?string {
    $path = null;
    if (str_starts_with($url, 'git@')) {
        $path = preg_replace('/^git@.*?:/', '', $url);
    } elseif (str_starts_with($url, 'https://')) {
        $parsed = parse_url($url);
        if (isset($parsed['path'])) {
            $path = ltrim($parsed['path'], '/');
        }
    }
    return $path ? preg_replace('/\.git$/', '', $path) : null;
}

/**
 * Determines the platform (github, gitlab, other) from a repo URL.
 */
function getRepoPlatform(string $url): string {
    if (str_contains($url, 'github.com')) return 'github';
    if (str_contains($url, 'git.drupalcode.org')) return 'gitlab';
    return 'other';
}

/**
 * Checks write access for a GitHub repository using the API.
 * Returns: 'read-write', 'read-only', 'inaccessible', or 'check_failed'.
 */
function checkGitHubAccess(Client $httpClient, string $repoPath, string $githubToken): string {
    $apiUrl = "https://api.github.com/repos/{$repoPath}";
    try {
        $response = $httpClient->request('GET', $apiUrl, [
            'headers' => [
                'Authorization' => "Bearer {$githubToken}",
                'Accept' => 'application/vnd.github.v3+json',
                'X-GitHub-Api-Version' => '2022-11-28',
            ],
            'http_errors' => true,
            'connect_timeout' => 5,
            'timeout' => 15,
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        if (isset($data['permissions'])) {
            if (!empty($data['permissions']['push']) || !empty($data['permissions']['admin'])) {
                return 'read-write';
            } else {
                return 'read-only';
            }
        } else {
            return 'read-only';
        }
    } catch (GuzzleHttp\Exception\ClientException $e) {
        $statusCode = $e->getResponse()->getStatusCode();
        if ($statusCode === 404) return 'inaccessible';
        if ($statusCode === 401 || $statusCode === 403) return 'read-only';
        return 'check_failed';
    } catch (GuzzleHttp\Exception\RequestException $e) {
        return 'check_failed';
    } catch (\Exception $e) {
        return 'check_failed';
    }
}

/**
 * Checks write access for a Drupal.org GitLab repository using the API.
 * Returns: 'read-write', 'read-only', 'inaccessible', or 'check_failed'.
 */
function checkGitLabAccess(Client $httpClient, string $repoPath, string $gitlabToken): string {
    $projectId = urlencode($repoPath);
    $apiUrl = "https://git.drupalcode.org/api/v4/projects/{$projectId}";
    try {
        $response = $httpClient->request('GET', $apiUrl, [
            'headers' => [
                'PRIVATE-TOKEN' => $gitlabToken,
                'Accept' => 'application/json',
            ],
            'http_errors' => true,
            'connect_timeout' => 5,
            'timeout' => 15,
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        $projectAccessLevel = $data['permissions']['project_access']['access_level'] ?? 0;
        $groupAccessLevel = $data['permissions']['group_access']['access_level'] ?? 0;
        $effectiveAccessLevel = max($projectAccessLevel, $groupAccessLevel);
        if ($effectiveAccessLevel >= 30) {
            return 'read-write';
        } else {
            return 'read-only';
        }
    } catch (GuzzleHttp\Exception\ClientException $e) {
        $statusCode = $e->getResponse()->getStatusCode();
        if ($statusCode === 404) return 'inaccessible';
        if ($statusCode === 401 || $statusCode === 403) return 'read-only';
        return 'check_failed';
    } catch (GuzzleHttp\Exception\RequestException $e) {
        return 'check_failed';
    } catch (\Exception $e) {
        return 'check_failed';
    }
}

/**
 * Loads API tokens from .env or environment variables.
 * Returns [githubToken, gitlabToken]
 */
function loadGitTokens(): array {
    // Try loading .env from current directory
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
    // Try loading .env from parent directory
    if (file_exists(__DIR__ . '/../.env')) {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();
    }
    $githubToken = $_ENV['GITHUB_TOKEN'] ?? getenv('GITHUB_TOKEN');
    $gitlabToken = $_ENV['GITLAB_DO_TOKEN'] ?? getenv('GITLAB_DO_TOKEN');
    return [$githubToken, $gitlabToken];
}

/**
 * Cleans and standardizes a Git repository URL, preferring git@ for write access.
 * Converts HTTPS to git@ if possible, ensures .git suffix, skips non-git URLs.
 */
function cleanRepoUrl(string $rawUrl): ?string {
    $url = trim($rawUrl);
    if (str_contains($url, 'drupal.org/project/')) { return null; }
    if (!str_starts_with($url, 'http') && !str_starts_with($url, 'git@')) { return null; }
    // Rewrite cgit.drupalcode.org to git.drupalcode.org (legacy)
    if (str_contains($url, 'cgit.drupalcode.org/')) {
        $url = str_replace('http://cgit.drupalcode.org/', 'https://git.drupalcode.org/', $url);
        if (!str_ends_with($url, '.git')) { $url .= '.git'; }
    }
    // Convert https or git@ for github to canonical SSH
    if (preg_match('#^https://github.com/([^/]+)/([^/]+)\.git$#', $url, $m)) {
        $url = 'git@github.com:' . $m[1] . '/' . $m[2] . '.git';
    }
    // Convert https for drupalcode to canonical SSH (git@git.drupal.org:project/NAME.git)
    elseif (preg_match('#^https://git.drupalcode.org/project/([^/]+)\.git$#', $url, $m)) {
        $url = 'git@git.drupal.org:project/' . $m[1] . '.git';
    }
    // Convert git@drupalcode.org to git@git.drupal.org
    elseif (preg_match('#^git@git\.drupalcode\.org:project/([^/]+)\.git$#', $url, $m)) {
        $url = 'git@git.drupal.org:project/' . $m[1] . '.git';
    }
    if ((str_contains($url, 'github.com') || str_contains($url, 'git.drupal.org')) && !str_ends_with($url, '.git')) {
        if (substr_count(ltrim(parse_url($url, PHP_URL_PATH) ?: '', '/'), '/') >= 1 || str_contains($url, ':')) { $url .= '.git'; }
        else { return null; }
    }
    if ((str_contains($url, 'github.com') || str_contains($url, 'git.drupal.org')) && preg_match('/^(https?:\/\/|git@).*\.git$/', $url)) { return $url; }
    return null;
} 