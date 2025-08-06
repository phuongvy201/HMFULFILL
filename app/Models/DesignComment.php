<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DesignComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'design_task_id',
        'user_id',
        'content',
        'type',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];

    // Constants cho type
    const TYPE_CUSTOMER = 'customer';
    const TYPE_DESIGNER = 'designer';

    /**
     * Get the design task that owns the comment.
     */
    public function designTask(): BelongsTo
    {
        return $this->belongsTo(DesignTask::class);
    }

    /**
     * Get the user that owns the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if comment is from customer
     */
    public function isFromCustomer(): bool
    {
        return $this->type === self::TYPE_CUSTOMER;
    }

    /**
     * Check if comment is from designer
     */
    public function isFromDesigner(): bool
    {
        return $this->type === self::TYPE_DESIGNER;
    }

    /**
     * Mark comment as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get user display name
     */
    public function getUserDisplayName(): string
    {
        return $this->user->first_name . ' ' . $this->user->last_name;
    }

    /**
     * Get user role display name
     */
    public function getUserRoleDisplayName(): string
    {
        return $this->type === self::TYPE_CUSTOMER ? 'Khách hàng' : 'Designer';
    }

    /**
     * Get formatted time for display
     */
    public function getFormattedTime(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }
}
