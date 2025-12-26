<?php

namespace WuriN7i\Balance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WuriN7i\Balance\Enums\TransactionAction;

/**
 * @property string $id
 * @property string $transaction_id
 * @property string $actor_id
 * @property TransactionAction $action
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read Transaction $transaction
 * @method static Builder ofAction(TransactionAction $action)
 * @method static Builder byActor(string $actorId)
 * @method static Builder approvals()
 * @method static Builder rejections()
 * @method static Builder voids()
 * @method static Builder recent()
 */
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
    public function scopeOfAction(Builder $query, TransactionAction $action): Builder
    {
        return $query->where($query->qualifyColumn('action'), $action);
    }

    /**
     * Scope to filter by actor.
     */
    public function scopeByActor(Builder $query, string $actorId): Builder
    {
        return $query->where($query->qualifyColumn('actor_id'), $actorId);
    }

    /**
     * Scope to get approval actions.
     */
    public function scopeApprovals(Builder $query): Builder
    {
        return $this->scopeOfAction($query, TransactionAction::APPROVE);
    }

    /**
     * Scope to get rejection actions.
     */
    public function scopeRejections(Builder $query): Builder
    {
        return $this->scopeOfAction($query, TransactionAction::REJECT);
    }

    /**
     * Scope to get void actions.
     */
    public function scopeVoids(Builder $query): Builder
    {
        return $this->scopeOfAction($query, TransactionAction::VOID);
    }

    /**
     * Scope to order by most recent.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest($query->qualifyColumn('created_at'));
    }
}
