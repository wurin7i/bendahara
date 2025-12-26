<?php

namespace WuriN7i\Balance\Contracts;

/**
 * Interface for providing actor/user identification.
 * 
 * This interface decouples the Balance module from specific User model implementations.
 * The application layer (e.g., Bendahara) should implement this interface to provide
 * the current user's ID when performing actions.
 */
interface ActorProviderInterface
{
    /**
     * Get the current actor's identifier.
     * 
     * This should return a unique identifier (UUID or string) for the user
     * performing the action. The Balance module stores this as a string
     * in transaction_logs.actor_id.
     * 
     * @return string The actor's unique identifier
     */
    public function getCurrentActorId(): string;

    /**
     * Check if there is a current actor.
     * 
     * @return bool True if an actor is authenticated/available
     */
    public function hasCurrentActor(): bool;
}
