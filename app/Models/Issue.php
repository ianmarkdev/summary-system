<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Issue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'priority',
        'category',
        'status',
        'summary',
        'next_action',
        'summary_source',
        'escalated',
        'escalated_at',
    ];

    protected $casts = [
        'escalated'    => 'boolean',
        'escalated_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Priority / category / status constants
    // -------------------------------------------------------------------------

    const PRIORITIES = ['low', 'medium', 'high', 'critical'];
    const CATEGORIES = ['bug', 'feature', 'infrastructure', 'security', 'other'];
    const STATUSES   = ['open', 'in_progress', 'resolved', 'closed'];

    // -------------------------------------------------------------------------
    // Query scopes (used by the controller for filtering)
    // -------------------------------------------------------------------------

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeEscalated(Builder $query): Builder
    {
        return $query->where('escalated', true);
    }

    // -------------------------------------------------------------------------
    // Business logic helpers
    // -------------------------------------------------------------------------

    /**
     * An issue needs escalation when:
     *  - priority is 'critical', OR
     *  - priority is 'high' and it has been open for more than 24 hours.
     */
    public function needsEscalation(): bool
    {
        if ($this->priority === 'critical') {
            return true;
        }

        if ($this->priority === 'high' && $this->status === 'open') {
            return $this->created_at->diffInHours(now()) >= 24;
        }

        return false;
    }

    public function escalate(): void
    {
        if (! $this->escalated) {
            $this->escalated    = true;
            $this->escalated_at = now();
            $this->save();
        }
    }

    // -------------------------------------------------------------------------
    // Presentation helpers (used in Blade views)
    // -------------------------------------------------------------------------

    public function priorityBadgeClass(): string
    {
        return match ($this->priority) {
            'critical' => 'badge-critical',
            'high'     => 'badge-high',
            'medium'   => 'badge-medium',
            default    => 'badge-low',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'open'        => 'badge-open',
            'in_progress' => 'badge-in-progress',
            'resolved'    => 'badge-resolved',
            default       => 'badge-closed',
        };
    }
}
