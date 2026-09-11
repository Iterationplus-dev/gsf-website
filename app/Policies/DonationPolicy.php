<?php

namespace App\Policies;

use App\Models\Donation;
use App\Models\User;

/**
 * Donations carry donor names, email addresses and amounts. Access is limited to
 * the finance role and to administrators, and nobody may edit or delete a
 * settled financial record through the interface - the transaction log is the
 * evidence that the money arrived.
 */
class DonationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('donations.view');
    }

    public function view(User $user, Donation $donation): bool
    {
        return $user->hasPermission('donations.view');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('donations.export');
    }

    public function resendReceipt(User $user, Donation $donation): bool
    {
        return $user->hasPermission('donations.receipt') && $donation->status === 'success';
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Donation $donation): bool
    {
        return false;
    }

    public function delete(User $user, Donation $donation): bool
    {
        return false;
    }
}
