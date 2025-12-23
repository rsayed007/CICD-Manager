<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepositoryChangeNotification extends Model
{
    protected $fillable = [
        'repo_name',
        'repository_file_id',
        'notification_type',
        'change_type',
        'file_path',
        'message',
        'sent',
        'sent_at',
        'metadata',
    ];

    protected $casts = [
        'sent' => 'boolean',
        'sent_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the repository file
     */
    public function repositoryFile(): BelongsTo
    {
        return $this->belongsTo(RepositoryFile::class, 'repository_file_id', 'record_id');
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'sent' => true,
            'sent_at' => now(),
        ]);
    }

    /**
     * Scope for unsent notifications
     */
    public function scopeUnsent($query)
    {
        return $query->where('sent', false);
    }

    /**
     * Scope for sent notifications
     */
    public function scopeSent($query)
    {
        return $query->where('sent', true);
    }

    /**
     * Scope for filtering by notification type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Scope for filtering by change type
     */
    public function scopeWithChangeType($query, string $type)
    {
        return $query->where('change_type', $type);
    }
}
