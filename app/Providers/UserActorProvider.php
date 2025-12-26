<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use WuriN7i\Balance\Contracts\ActorProviderInterface;

class UserActorProvider implements ActorProviderInterface
{
    /**
     * Get the current actor ID (authenticated user ID).
     */
    public function getCurrentActorId(): string
    {
        // Return authenticated user ID or 'system' for unauthenticated actions
        if (Auth::check()) {
            return (string) Auth::id();
        }

        return 'system';
    }

    /**
     * Check if there is a current actor.
     */
    public function hasCurrentActor(): bool
    {
        return Auth::check();
    }

    /**
     * Get actor display name for logging.
     */
    public function getActorName(): string
    {
        if (Auth::check()) {
            return Auth::user()->name ?? Auth::user()->email ?? 'User #'.Auth::id();
        }

        return 'System';
    }

    /**
     * Check if current actor has permission to perform an action.
     *
     * @param  string  $action  Action name (e.g., 'approve_transaction', 'create_transaction')
     */
    public function can(string $action): bool
    {
        // Simple permission check - can be extended with Spatie Permission or custom logic
        if (! Auth::check()) {
            // System actions are allowed
            return true;
        }

        // For now, all authenticated users can perform basic actions
        // TODO: Implement proper authorization (e.g., using Laravel Policies)
        return match ($action) {
            'create_transaction' => true,
            'approve_transaction' => Auth::user()->can('approve_transactions') ?? false,
            'reject_transaction' => Auth::user()->can('approve_transactions') ?? false,
            default => false,
        };
    }
}
