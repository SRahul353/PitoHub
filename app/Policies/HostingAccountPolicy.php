<?php

namespace App\Policies;

use App\Models\HostingAccount;
use App\Models\User;

class HostingAccountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Filtered in controller based on role
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, HostingAccount $hostingAccount): bool
    {
        return $user->isAdmin() || $hostingAccount->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, HostingAccount $hostingAccount): bool
    {
        return $user->isAdmin() || $hostingAccount->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, HostingAccount $hostingAccount): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage resources (emails, dbs, cron, domains).
     */
    public function manageResources(User $user, HostingAccount $hostingAccount): bool
    {
        return $user->isAdmin() || $hostingAccount->user_id === $user->id;
    }

    /**
     * Determine whether the user can change password.
     */
    public function changePassword(User $user, HostingAccount $hostingAccount): bool
    {
        return $user->isAdmin() || $hostingAccount->user_id === $user->id;
    }

    /**
     * Determine whether the user can access SSO.
     */
    public function accessSso(User $user, HostingAccount $hostingAccount): bool
    {
        return $user->isAdmin() || $hostingAccount->user_id === $user->id;
    }

    /**
     * Determine whether the user can suspend/unsuspend.
     */
    public function toggleSuspension(User $user, HostingAccount $hostingAccount): bool
    {
        return $user->isAdmin();
    }
}
