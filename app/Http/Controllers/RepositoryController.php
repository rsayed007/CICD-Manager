<?php

namespace App\Http\Controllers;

use App\Services\GitRepositoryService;
use App\Models\RepositoryFile;
use App\Models\RepositoryChangeNotification;
use App\Models\RepositorySyncLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RepositoryController extends Controller
{
    /**
     * Sync repository and detect changes
     */
    public function sync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'repo_url' => 'required|url',
            'repo_name' => 'required|string|max:255',
            'branch' => 'string|max:255',
            'token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $repoUrl = $request->input('repo_url');
        $repoName = $request->input('repo_name');
        $branch = $request->input('branch', 'main');
        $token = $request->input('token');

        try {
            $service = new GitRepositoryService($repoUrl, $token);
            $result = $service->syncRepository($repoName, $branch);

            if ($result['status'] === 'success') {
                // Build directory tree
                $tree = $service->buildDirectoryTree($repoName);
                $treeString = $service->formatTreeAsString($tree);

                // Get change lists
                $newFiles = RepositoryFile::forRepo($repoName)
                    ->withChangeStatus('new')
                    ->get()
                    ->map(fn($f) => [
                        'path' => $f->file_path,
                        'type' => $f->file_type,
                        'size' => $f->size,
                    ]);

                $modifiedFiles = RepositoryFile::forRepo($repoName)
                    ->withChangeStatus('modified')
                    ->get()
                    ->map(fn($f) => [
                        'path' => $f->file_path,
                        'type' => $f->file_type,
                        'size' => $f->size,
                    ]);

                $deletedFiles = RepositoryFile::forRepo($repoName)
                    ->withChangeStatus('deleted')
                    ->get()
                    ->map(fn($f) => [
                        'path' => $f->file_path,
                        'type' => $f->file_type,
                    ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Repository synced successfully',
                    'data' => [
                        'sync_log_id' => $result['sync_log_id'],
                        'runtime_seconds' => $result['runtime_seconds'],
                        'statistics' => $result['stats'],
                        'directory_tree' => $tree,
                        'directory_tree_string' => $treeString,
                        'changes' => [
                            'new' => $newFiles,
                            'modified' => $modifiedFiles,
                            'deleted' => $deletedFiles,
                        ],
                    ],
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Repository sync failed',
                    'error' => $result['error'],
                    'sync_log_id' => $result['sync_log_id'],
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during sync',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get repository structure
     */
    public function getStructure(Request $request, string $repoName): JsonResponse
    {
        try {
            $service = new GitRepositoryService('', null);
            $tree = $service->buildDirectoryTree($repoName);
            $treeString = $service->formatTreeAsString($tree);

            $files = RepositoryFile::forRepo($repoName)
                ->orderBy('file_path')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'repo_name' => $repoName,
                    'total_files' => $files->count(),
                    'directory_tree' => $tree,
                    'directory_tree_string' => $treeString,
                    'files' => $files->map(fn($f) => [
                        'record_id' => $f->record_id,
                        'file_path' => $f->file_path,
                        'folder_path' => $f->folder_path,
                        'file_type' => $f->file_type,
                        'size' => $f->size,
                        'change_status' => $f->change_status,
                        'last_checked_at' => $f->last_checked_at,
                    ]),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get repository structure',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get changes for a repository
     */
    public function getChanges(Request $request, string $repoName): JsonResponse
    {
        try {
            $changeStatus = $request->input('status'); // new, modified, deleted

            $query = RepositoryFile::forRepo($repoName);

            if ($changeStatus) {
                $query->withChangeStatus($changeStatus);
            } else {
                // Get all changes (exclude unchanged)
                $query->whereIn('change_status', ['new', 'modified', 'deleted']);
            }

            $files = $query->orderBy('updated_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'repo_name' => $repoName,
                    'filter' => $changeStatus ?? 'all_changes',
                    'total' => $files->count(),
                    'changes' => $files->map(fn($f) => [
                        'record_id' => $f->record_id,
                        'file_path' => $f->file_path,
                        'file_type' => $f->file_type,
                        'change_status' => $f->change_status,
                        'size' => $f->size,
                        'updated_at' => $f->updated_at,
                    ]),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get changes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get notifications
     */
    public function getNotifications(Request $request, string $repoName): JsonResponse
    {
        try {
            $sent = $request->input('sent'); // true/false
            $type = $request->input('type'); // email, ui_alert, message_log
            $changeType = $request->input('change_type'); // new, modified, deleted

            $query = RepositoryChangeNotification::where('repo_name', $repoName);

            if ($sent !== null) {
                $query->where('sent', filter_var($sent, FILTER_VALIDATE_BOOLEAN));
            }

            if ($type) {
                $query->ofType($type);
            }

            if ($changeType) {
                $query->withChangeType($changeType);
            }

            $notifications = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'repo_name' => $repoName,
                    'total' => $notifications->count(),
                    'notifications' => $notifications->map(fn($n) => [
                        'id' => $n->id,
                        'notification_type' => $n->notification_type,
                        'change_type' => $n->change_type,
                        'file_path' => $n->file_path,
                        'message' => $n->message,
                        'sent' => $n->sent,
                        'sent_at' => $n->sent_at,
                        'created_at' => $n->created_at,
                    ]),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sync logs
     */
    public function getSyncLogs(Request $request, string $repoName): JsonResponse
    {
        try {
            $status = $request->input('status'); // pending, running, completed, failed

            $query = RepositorySyncLog::where('repo_name', $repoName);

            if ($status) {
                $query->withStatus($status);
            }

            $logs = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'repo_name' => $repoName,
                    'total' => $logs->count(),
                    'logs' => $logs->map(fn($l) => [
                        'id' => $l->id,
                        'status' => $l->status,
                        'started_at' => $l->started_at,
                        'completed_at' => $l->completed_at,
                        'runtime_seconds' => $l->runtime_seconds,
                        'files_scanned' => $l->files_scanned,
                        'new_files' => $l->new_files,
                        'modified_files' => $l->modified_files,
                        'deleted_files' => $l->deleted_files,
                        'total_changes' => $l->total_changes,
                        'error_message' => $l->error_message,
                        'created_at' => $l->created_at,
                    ]),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get sync logs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get latest sync status
     */
    public function getLatestSync(string $repoName): JsonResponse
    {
        try {
            $latestSync = RepositorySyncLog::where('repo_name', $repoName)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$latestSync) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No sync logs found for this repository',
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $latestSync->id,
                    'repo_name' => $latestSync->repo_name,
                    'status' => $latestSync->status,
                    'started_at' => $latestSync->started_at,
                    'completed_at' => $latestSync->completed_at,
                    'runtime_seconds' => $latestSync->runtime_seconds,
                    'statistics' => [
                        'files_scanned' => $latestSync->files_scanned,
                        'new_files' => $latestSync->new_files,
                        'modified_files' => $latestSync->modified_files,
                        'deleted_files' => $latestSync->deleted_files,
                        'total_changes' => $latestSync->total_changes,
                    ],
                    'error_message' => $latestSync->error_message,
                    'created_at' => $latestSync->created_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get latest sync',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark notification as sent
     */
    public function markNotificationSent(int $notificationId): JsonResponse
    {
        try {
            $notification = RepositoryChangeNotification::findOrFail($notificationId);
            $notification->markAsSent();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as sent',
                'data' => [
                    'id' => $notification->id,
                    'sent' => $notification->sent,
                    'sent_at' => $notification->sent_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark notification as sent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
