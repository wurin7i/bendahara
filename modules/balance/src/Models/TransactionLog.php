<?php

namespace WuriN7i\Balance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WuriN7i\Balance\Enums\TransactionAction;

class TransactionLog extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'transaction_logs';

    /**
     * Indicates if the model should be timestamped.
     * Only created_at is used (no updated_at).
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'transaction_id',
        'actor_id',
        'action',
        'comment',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'action' => TransactionAction::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set created_at when creating
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    /**
     * Get the transaction that this log belongs to.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Check if this action requires a comment.
     */
    public function requiresComment(): bool
    {
        return $this->action->requiresComment();
    }

    /**
     * Get the resulting status from this action.
     */
    public function resultingStatus(): ?string
    {
        return $this->action->resultingStatus()?->value;
    }

    /**
     * Scope to filter by action.
     */
    public function scopeOfAction($query, TransactionAction $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by actor.
     */
    public function scopeByActor($query, string $actorId)
    {
        return $query->where('actor_id', $actorId);
    }

    /**
     * Scope to get approval actions.
     */
    public function scopeApprovals($query)
    {
        return $query->where('action', TransactionAction::APPROVE);
    }

    /**
     * Scope to get rejection actions.
     */
    public function scopeRejections($query)
    {
        return $query->where('action', TransactionAction::REJECT);
    }

    /**
     * Scope to get void actions.
     */
    public function scopeVoids($query)
    {
        return $query->where('action', TransactionAction::VOID);
    }

    /**
     * Scope to order by most recent.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
