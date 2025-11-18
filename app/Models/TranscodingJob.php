<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranscodingJob extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'content_item_id',
        'episode_id',
        'source_key',
        'output_path',
        'status',
        'progress_percentage',
        'result',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'result' => 'array',
        'progress_percentage' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the content item for the job.
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    /**
     * Get the episode for the job.
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Scope a query to only include pending jobs.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['queued', 'processing']);
    }

    /**
     * Scope a query to only include completed jobs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed jobs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Mark job as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark job as completed.
     */
    public function markAsCompleted(array $result = []): void
    {
        $this->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'result' => $result,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark job as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }
}
