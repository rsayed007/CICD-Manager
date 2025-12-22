<?php

namespace App\Services;

use App\Models\RepositoryFile;
use App\Models\RepositoryChangeNotification;
use App\Models\RepositorySyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GitRepositoryService
{
    protected string $apiUrl;
    protected ?string $token;
    protected string $platform; // github, gitlab, bitbucket

    /**
     * Parse repository URL and initialize service
     */
    public function __construct(?string $repoUrl = null, ?string $token = null)
    {
        $this->token = $token;
        $this->parseRepoUrl($repoUrl);
    }

    /**
     * Parse repository URL to determine platform and API endpoint
     */
    protected function parseRepoUrl(?string $repoUrl): void
    {
        // Detect platform from URL
        if (str_contains($repoUrl, 'github.com')) {
            $this->platform = 'github';
        } elseif (str_contains($repoUrl, 'gitlab.com')) {
            $this->platform = 'gitlab';
        } elseif (str_contains($repoUrl, 'bitbucket.org')) {
            $this->platform = 'bitbucket';
        } else {
            // Generic Git API
            $this->platform = 'generic';
        }

        $this->apiUrl = $repoUrl?? 'github.com';
    }

    /**
     * Fetch complete repository structure
     */
    public function fetchRepositoryStructure(string $repoName, string $branch = 'main'): array
    {
        try {
            switch ($this->platform) {
                case 'github':
                    return $this->fetchGitHubStructure($repoName, $branch);
                case 'gitlab':
                    return $this->fetchGitLabStructure($repoName, $branch);
                case 'bitbucket':
                    return $this->fetchBitbucketStructure($repoName, $branch);
                default:
                    return $this->fetchGenericStructure($repoName, $branch);
            }
        } catch (Exception $e) {
            Log::error("Failed to fetch repository structure: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch GitHub repository structure using Git Trees API
     */
    protected function fetchGitHubStructure(string $repoName, string $branch): array
    {
        // Extract owner and repo from API URL
        // Expected format: https://api.github.com/repos/{owner}/{repo}
        $urlParts = parse_url($this->apiUrl);
        $pathParts = explode('/', trim($urlParts['path'] ?? '', '/'));
        
        if (count($pathParts) >= 3 && $pathParts[0] === 'repos') {
            $owner = $pathParts[1];
            $repo = $pathParts[2];
        } else {
            throw new Exception("Invalid GitHub API URL format");
        }

        // First, get the SHA of the branch
        $branchUrl = "https://api.github.com/repos/{$owner}/{$repo}/branches/{$branch}";
        $branchResponse = $this->makeRequest($branchUrl);
        
        if (!isset($branchResponse['commit']['sha'])) {
            throw new Exception("Could not fetch branch information");
        }

        $treeSha = $branchResponse['commit']['commit']['tree']['sha'];

        // Fetch the complete tree recursively
        $treeUrl = "https://api.github.com/repos/{$owner}/{$repo}/git/trees/{$treeSha}?recursive=1";
        $treeResponse = $this->makeRequest($treeUrl);

        $files = [];
        if (isset($treeResponse['tree'])) {
            foreach ($treeResponse['tree'] as $item) {
                $files[] = [
                    'path' => $item['path'],
                    'type' => $item['type'] === 'tree' ? 'dir' : 'file',
                    'size' => $item['size'] ?? null,
                    'sha' => $item['sha'] ?? null,
                    'url' => $item['url'] ?? null,
                ];
            }
        }

        return $files;
    }

    /**
     * Fetch GitLab repository structure
     */
    protected function fetchGitLabStructure(string $repoName, string $branch): array
    {
        // GitLab API format: https://gitlab.com/api/v4/projects/{id}/repository/tree
        $url = $this->apiUrl . "/repository/tree?recursive=true&ref={$branch}&per_page=100";
        
        $files = [];
        $page = 1;
        
        do {
            $response = $this->makeRequest($url . "&page={$page}");
            
            foreach ($response as $item) {
                $files[] = [
                    'path' => $item['path'],
                    'type' => $item['type'] === 'tree' ? 'dir' : 'file',
                    'size' => null, // GitLab tree API doesn't return size
                    'sha' => $item['id'] ?? null,
                    'url' => null,
                ];
            }
            
            $page++;
        } while (count($response) === 100); // Continue if there might be more pages

        return $files;
    }

    /**
     * Fetch Bitbucket repository structure
     */
    protected function fetchBitbucketStructure(string $repoName, string $branch): array
    {
        // Bitbucket API format: https://api.bitbucket.org/2.0/repositories/{workspace}/{repo_slug}/src/{commit}/{path}
        $url = $this->apiUrl . "/src/{$branch}/?pagelen=100";
        
        $files = [];
        
        do {
            $response = $this->makeRequest($url);
            
            if (isset($response['values'])) {
                foreach ($response['values'] as $item) {
                    $files[] = [
                        'path' => $item['path'],
                        'type' => $item['type'] === 'commit_directory' ? 'dir' : 'file',
                        'size' => $item['size'] ?? null,
                        'sha' => $item['commit']['hash'] ?? null,
                        'url' => null,
                    ];
                }
            }
            
            $url = $response['next'] ?? null;
        } while ($url);

        return $files;
    }

    /**
     * Fetch generic Git repository structure
     */
    protected function fetchGenericStructure(string $repoName, string $branch): array
    {
        // For generic APIs, try to use a standard format
        $response = $this->makeRequest($this->apiUrl);
        
        // This is a placeholder - actual implementation depends on the API format
        return $response['files'] ?? [];
    }

    /**
     * Make HTTP request with authentication
     */
    protected function makeRequest(string $url): array
    {
        $request = Http::timeout(30);
        
        if ($this->token) {
            if ($this->platform === 'github') {
                $request = $request->withToken($this->token);
            } elseif ($this->platform === 'gitlab') {
                $request = $request->withHeaders(['PRIVATE-TOKEN' => $this->token]);
            } elseif ($this->platform === 'bitbucket') {
                $request = $request->withBasicAuth('x-token-auth', $this->token);
            }
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = $request->accept('application/json')->get($url);

        if (!$response->successful()) {
            throw new Exception("API request failed: " . $response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Sync repository and detect changes
     */
    public function syncRepository(string $repoName, string $branch = 'main'): array
    {
        // Create sync log
        $syncLog = RepositorySyncLog::create([
            'repo_name' => $repoName,
            'repo_url' => $this->apiUrl,
            'status' => 'pending',
        ]);

        try {
            $syncLog->markAsStarted();

            // Fetch current repository structure
            $currentFiles = $this->fetchRepositoryStructure($repoName, $branch);
            
            // Get existing files from database
            $existingFiles = RepositoryFile::forRepo($repoName)->get()->keyBy('file_path');
            
            $stats = [
                'scanned' => count($currentFiles),
                'new' => 0,
                'modified' => 0,
                'deleted' => 0,
            ];

            $changes = [
                'new' => [],
                'modified' => [],
                'deleted' => [],
            ];

            // Process current files
            foreach ($currentFiles as $fileData) {
                $filePath = $fileData['path'];
                $existingFile = $existingFiles->get($filePath);

                if (!$existingFile) {
                    // New file
                    $file = $this->createRepositoryFile($repoName, $fileData);
                    $file->markAsNew();
                    $this->createNotification($repoName, $file, 'new');
                    $stats['new']++;
                    $changes['new'][] = $filePath;
                } else {
                    // Check if modified
                    if ($this->isFileModified($existingFile, $fileData)) {
                        $this->updateRepositoryFile($existingFile, $fileData);
                        $existingFile->markAsModified();
                        $this->createNotification($repoName, $existingFile, 'modified');
                        $stats['modified']++;
                        $changes['modified'][] = $filePath;
                    } else {
                        // Mark as unchanged
                        $existingFile->markAsUnchanged();
                    }
                    
                    // Update last checked timestamp
                    $existingFile->update(['last_checked_at' => now()]);
                    
                    // Remove from existing files collection
                    $existingFiles->forget($filePath);
                }
            }

            // Remaining files in $existingFiles are deleted
            foreach ($existingFiles as $deletedFile) {
                $deletedFile->markAsDeleted();
                $this->createNotification($repoName, $deletedFile, 'deleted');
                $stats['deleted']++;
                $changes['deleted'][] = $deletedFile->file_path;
            }

            // Update sync log
            $syncLog->updateStats($stats['scanned'], $stats['new'], $stats['modified'], $stats['deleted']);
            $syncLog->markAsCompleted();

            return [
                'status' => 'success',
                'sync_log_id' => $syncLog->id,
                'stats' => $stats,
                'changes' => $changes,
                'runtime_seconds' => $syncLog->runtime_seconds,
            ];

        } catch (Exception $e) {
            $syncLog->markAsFailed($e->getMessage());
            
            return [
                'status' => 'error',
                'sync_log_id' => $syncLog->id,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create repository file record
     */
    protected function createRepositoryFile(string $repoName, array $fileData): RepositoryFile
    {
        $folderPath = dirname($fileData['path']);
        if ($folderPath === '.') {
            $folderPath = null;
        }

        return RepositoryFile::create([
            'repo_name' => $repoName,
            'repo_url' => $this->apiUrl,
            'file_path' => $fileData['path'],
            'folder_path' => $folderPath,
            'file_type' => $fileData['type'],
            'size' => $fileData['size'],
            'sha' => $fileData['sha'],
            'last_checked_at' => now(),
        ]);
    }

    /**
     * Update repository file record
     */
    protected function updateRepositoryFile(RepositoryFile $file, array $fileData): void
    {
        $file->update([
            'size' => $fileData['size'],
            'sha' => $fileData['sha'],
            'last_checked_at' => now(),
        ]);
    }

    /**
     * Check if file is modified
     */
    protected function isFileModified(RepositoryFile $existingFile, array $newFileData): bool
    {
        // Compare SHA if available
        if ($existingFile->sha && $newFileData['sha']) {
            return $existingFile->sha !== $newFileData['sha'];
        }

        // Fallback to size comparison
        if ($existingFile->size && $newFileData['size']) {
            return $existingFile->size !== $newFileData['size'];
        }

        return false;
    }

    /**
     * Create change notification
     */
    protected function createNotification(string $repoName, RepositoryFile $file, string $changeType): void
    {
        $message = $this->generateNotificationMessage($file, $changeType);

        // Create notifications for different channels
        $notificationTypes = ['email', 'ui_alert', 'message_log'];

        foreach ($notificationTypes as $type) {
            RepositoryChangeNotification::create([
                'repo_name' => $repoName,
                'repository_file_id' => $file->record_id,
                'notification_type' => $type,
                'change_type' => $changeType,
                'file_path' => $file->file_path,
                'message' => $message,
                'metadata' => [
                    'file_type' => $file->file_type,
                    'size' => $file->size,
                    'sha' => $file->sha,
                ],
            ]);
        }
    }

    /**
     * Generate notification message
     */
    protected function generateNotificationMessage(RepositoryFile $file, string $changeType): string
    {
        $typeLabel = $file->file_type === 'dir' ? 'Directory' : 'File';
        
        switch ($changeType) {
            case 'new':
                return "{$typeLabel} '{$file->file_path}' was added to the repository";
            case 'modified':
                return "{$typeLabel} '{$file->file_path}' was modified";
            case 'deleted':
                return "{$typeLabel} '{$file->file_path}' was deleted from the repository";
            default:
                return "{$typeLabel} '{$file->file_path}' changed";
        }
    }

    /**
     * Build directory tree structure
     */
    public function buildDirectoryTree(string $repoName): array
    {
        $files = RepositoryFile::forRepo($repoName)
            ->orderBy('file_path')
            ->get();

        $tree = [];

        foreach ($files as $file) {
            $parts = explode('/', $file->file_path);
            $current = &$tree;

            foreach ($parts as $index => $part) {
                if ($index === count($parts) - 1) {
                    // Last part - this is the file/folder itself
                    $current[$part] = [
                        'type' => $file->file_type,
                        'size' => $file->size,
                        'change_status' => $file->change_status,
                        'record_id' => $file->record_id,
                    ];
                } else {
                    // Intermediate directory
                    if (!isset($current[$part])) {
                        $current[$part] = [];
                    }
                    $current = &$current[$part];
                }
            }
        }

        return $tree;
    }

    /**
     * Format tree as string
     */
    public function formatTreeAsString(array $tree, int $indent = 0): string
    {
        $output = '';
        
        foreach ($tree as $name => $data) {
            $prefix = str_repeat('  ', $indent);
            
            if (is_array($data) && isset($data['type'])) {
                // This is a file or directory entry
                $icon = $data['type'] === 'dir' ? '📁' : '📄';
                $status = $data['change_status'] !== 'unchanged' ? " [{$data['change_status']}]" : '';
                $output .= "{$prefix}{$icon} {$name}{$status}\n";
            } else {
                // This is a nested structure
                $output .= "{$prefix}📁 {$name}/\n";
                $output .= $this->formatTreeAsString($data, $indent + 1);
            }
        }
        
        return $output;
    }
}
