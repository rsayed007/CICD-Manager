<?php

namespace App\Http\Controllers;

use App\Models\RepositoryFile;
use App\Models\RepositoryChangeNotification;
use App\Models\RepositorySyncLog;
use App\Services\GitRepositoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepositoryWebController extends Controller
{
    /**
     * Display repository dashboard
     */
    public function index()
    {
        // Get all unique repositories
        $repositories = RepositoryFile::select('repo_name', 'repo_url')
            ->selectRaw('MAX(updated_at) as last_updated')
            ->selectRaw('COUNT(*) as total_files')
            ->selectRaw('SUM(CASE WHEN change_status = "new" THEN 1 ELSE 0 END) as new_files')
            ->selectRaw('SUM(CASE WHEN change_status = "modified" THEN 1 ELSE 0 END) as modified_files')
            ->selectRaw('SUM(CASE WHEN change_status = "deleted" THEN 1 ELSE 0 END) as deleted_files')
            ->groupBy('repo_name', 'repo_url')
            ->get();

        // Get latest sync logs for each repository
        $syncLogs = RepositorySyncLog::select('repo_name')
            ->selectRaw('MAX(created_at) as last_sync')
            ->selectRaw('(SELECT status FROM repository_sync_logs WHERE repo_name = repository_sync_logs.repo_name ORDER BY created_at DESC LIMIT 1) as last_status')
            ->groupBy('repo_name')
            ->get()
            ->keyBy('repo_name');

        return view('repositories.index', compact('repositories', 'syncLogs'));
    }

    /**
     * Sync repository
     */
    public function sync(Request $request)
    {
        $request->validate([
            'repo_url' => 'required|url',
            'repo_name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'token' => 'nullable|string',
        ]);

        try {
            $service = new GitRepositoryService(
                $request->repo_url,
                $request->token
            );

            $result = $service->syncRepository(
                $request->repo_name,
                $request->branch ?? 'main'
            );

            if ($result['status'] === 'success') {
                return redirect()
                    ->route('repositories.show', $request->repo_name)
                    ->with('success', "Repository synced successfully! Scanned {$result['stats']['scanned']} files, found {$result['stats']['new']} new, {$result['stats']['modified']} modified, {$result['stats']['deleted']} deleted.");
            } else {
                return back()
                    ->with('error', 'Sync failed: ' . ($result['error'] ?? 'Unknown error'))
                    ->withInput();
            }
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show repository details
     */
    public function show(string $repoName)
    {
        $repository = RepositoryFile::where('repo_name', $repoName)
            ->first();

        if (!$repository) {
            return redirect()
                ->route('repositories.index')
                ->with('error', 'Repository not found');
        }

        // Get statistics
        $stats = [
            'total_files' => RepositoryFile::forRepo($repoName)->count(),
            'new_files' => RepositoryFile::forRepo($repoName)->withChangeStatus('new')->count(),
            'modified_files' => RepositoryFile::forRepo($repoName)->withChangeStatus('modified')->count(),
            'deleted_files' => RepositoryFile::forRepo($repoName)->withChangeStatus('deleted')->count(),
            'directories' => RepositoryFile::forRepo($repoName)->directoriesOnly()->count(),
            'files' => RepositoryFile::forRepo($repoName)->filesOnly()->count(),
        ];

        // Get latest sync log
        $latestSync = RepositorySyncLog::where('repo_name', $repoName)
            ->orderBy('created_at', 'desc')
            ->first();

        // Get recent changes
        $recentChanges = RepositoryFile::forRepo($repoName)
            ->whereIn('change_status', ['new', 'modified', 'deleted'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Get unsent notifications count
        $unsentNotifications = RepositoryChangeNotification::where('repo_name', $repoName)
            ->where('sent', false)
            ->count();

        return view('repositories.show', compact(
            'repository',
            'repoName',
            'stats',
            'latestSync',
            'recentChanges',
            'unsentNotifications'
        ));
    }

    /**
     * Show directory structure
     */
    public function structure(string $repoName)
    {
        $repository = RepositoryFile::where('repo_name', $repoName)->first();

        if (!$repository) {
            return redirect()
                ->route('repositories.index')
                ->with('error', 'Repository not found');
        }

        $service = new GitRepositoryService('', null);
        $tree = $service->buildDirectoryTree($repoName);
        $treeString = $service->formatTreeAsString($tree);

        $files = RepositoryFile::forRepo($repoName)
            ->orderBy('file_path')
            ->get();

        return view('repositories.structure', compact(
            'repoName',
            'repository',
            'tree',
            'treeString',
            'files'
        ));
    }

    /**
     * Show changes
     */
    public function changes(Request $request, string $repoName)
    {
        $repository = RepositoryFile::where('repo_name', $repoName)->first();

        if (!$repository) {
            return redirect()
                ->route('repositories.index')
                ->with('error', 'Repository not found');
        }

        $status = $request->get('status', 'all');

        $query = RepositoryFile::forRepo($repoName);

        if ($status !== 'all') {
            $query->withChangeStatus($status);
        } else {
            $query->whereIn('change_status', ['new', 'modified', 'deleted']);
        }

        $changes = $query->orderBy('updated_at', 'desc')->paginate(50);

        $stats = [
            'new' => RepositoryFile::forRepo($repoName)->withChangeStatus('new')->count(),
            'modified' => RepositoryFile::forRepo($repoName)->withChangeStatus('modified')->count(),
            'deleted' => RepositoryFile::forRepo($repoName)->withChangeStatus('deleted')->count(),
        ];

        return view('repositories.changes', compact(
            'repoName',
            'repository',
            'changes',
            'status',
            'stats'
        ));
    }

    /**
     * Show notifications
     */
    public function notifications(Request $request, string $repoName)
    {
        $repository = RepositoryFile::where('repo_name', $repoName)->first();

        if (!$repository) {
            return redirect()
                ->route('repositories.index')
                ->with('error', 'Repository not found');
        }

        $type = $request->get('type', 'all');
        $sent = $request->get('sent', 'all');

        $query = RepositoryChangeNotification::where('repo_name', $repoName);

        if ($type !== 'all') {
            $query->ofType($type);
        }

        if ($sent === 'sent') {
            $query->sent();
        } elseif ($sent === 'unsent') {
            $query->unsent();
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(50);

        $stats = [
            'total' => RepositoryChangeNotification::where('repo_name', $repoName)->count(),
            'sent' => RepositoryChangeNotification::where('repo_name', $repoName)->sent()->count(),
            'unsent' => RepositoryChangeNotification::where('repo_name', $repoName)->unsent()->count(),
        ];

        return view('repositories.notifications', compact(
            'repoName',
            'repository',
            'notifications',
            'type',
            'sent',
            'stats'
        ));
    }

    /**
     * Show sync logs
     */
    public function logs(Request $request, string $repoName)
    {
        $repository = RepositoryFile::where('repo_name', $repoName)->first();

        if (!$repository) {
            return redirect()
                ->route('repositories.index')
                ->with('error', 'Repository not found');
        }

        $status = $request->get('status', 'all');

        $query = RepositorySyncLog::where('repo_name', $repoName);

        if ($status !== 'all') {
            $query->withStatus($status);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => RepositorySyncLog::where('repo_name', $repoName)->count(),
            'completed' => RepositorySyncLog::where('repo_name', $repoName)->completed()->count(),
            'failed' => RepositorySyncLog::where('repo_name', $repoName)->failed()->count(),
        ];

        return view('repositories.logs', compact(
            'repoName',
            'repository',
            'logs',
            'status',
            'stats'
        ));
    }
}
