<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositorySyncLog extends Model
{
    protected $fillable = [
        'repo_name',
        'repo_url',
        'status',
        'started_at',
        'completed_at',
        'runtime_seconds',
        'files_scanned',
        'new_files',
        'modified_files',
        'deleted_files',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'runtime_seconds' => 'integer',
        'files_scanned' => 'integer',
        'new_files' => 'integer',
        'modified_files' => 'integer',
        'deleted_files' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Mark sync as started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark sync as completed
     */
    public function markAsCompleted(): void
    {
        $completedAt = now();
        $runtimeSeconds = $this->started_at ? $completedAt->diffInSeconds($this->started_at) : 0;

        $this->update([
            'status' => 'completed',
            'completed_at' => $completedAt,
            'runtime_seconds' => $runtimeSeconds,
        ]);
    }

    /**
     * Mark sync as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $completedAt = now();
        $runtimeSeconds = $this->started_at ? $completedAt->diffInSeconds($this->started_at) : 0;

        $this->update([
            'status' => 'failed',
            'completed_at' => $completedAt,
            'runtime_seconds' => $runtimeSeconds,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Update statistics
     */
    public function updateStats(int $scanned, int $new, int $modified, int $deleted): void
    {
        $this->update([
            'files_scanned' => $scanned,
            'new_files' => $new,
            'modified_files' => $modified,
            'deleted_files' => $deleted,
        ]);
    }

    /**
     * Get total changes
     */
    public function getTotalChangesAttribute(): int
    {
        return $this->new_files + $this->modified_files + $this->deleted_files;
    }

    /**
     * Scope for filtering by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for completed syncs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
