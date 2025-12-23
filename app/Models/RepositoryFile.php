<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RepositoryFile extends Model
{
    protected $primaryKey = 'record_id';
    
    protected $fillable = [
        'repo_name',
        'repo_url',
        'file_path',
        'folder_path',
        'file_type',
        'size',
        'sha',
        'commit_date',
        'last_commit_sha',
        'last_commit_message',
        'change_status',
        'last_checked_at',
    ];

    protected $casts = [
        'commit_date' => 'datetime',
        'last_checked_at' => 'datetime',
        'size' => 'integer',
    ];

    /**
     * Get notifications for this file
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(RepositoryChangeNotification::class, 'repository_file_id', 'record_id');
    }

    /**
     * Check if file is new
     */
    public function isNew(): bool
    {
        return $this->change_status === 'new';
    }

    /**
     * Check if file is modified
     */
    public function isModified(): bool
    {
        return $this->change_status === 'modified';
    }

    /**
     * Check if file is deleted
     */
    public function isDeleted(): bool
    {
        return $this->change_status === 'deleted';
    }

    /**
     * Mark as new
     */
    public function markAsNew(): void
    {
        $this->update(['change_status' => 'new']);
    }

    /**
     * Mark as modified
     */
    public function markAsModified(): void
    {
        $this->update(['change_status' => 'modified']);
    }

    /**
     * Mark as deleted
     */
    public function markAsDeleted(): void
    {
        $this->update(['change_status' => 'deleted']);
    }

    /**
     * Mark as unchanged
     */
    public function markAsUnchanged(): void
    {
        $this->update(['change_status' => 'unchanged']);
    }

    /**
     * Scope for filtering by repository
     */
    public function scopeForRepo($query, string $repoName)
    {
        return $query->where('repo_name', $repoName);
    }

    /**
     * Scope for filtering by change status
     */
    public function scopeWithChangeStatus($query, string $status)
    {
        return $query->where('change_status', $status);
    }

    /**
     * Scope for files only
     */
    public function scopeFilesOnly($query)
    {
        return $query->where('file_type', 'file');
    }

    /**
     * Scope for directories only
     */
    public function scopeDirectoriesOnly($query)
    {
        return $query->where('file_type', 'dir');
    }
}
