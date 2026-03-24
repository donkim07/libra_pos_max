<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TransferFunds;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransferFundsPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TransferFunds');
    }

    public function view(AuthUser $authUser, TransferFunds $transferFunds): bool
    {
        return $authUser->can('View:TransferFunds');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TransferFunds');
    }

    public function update(AuthUser $authUser, TransferFunds $transferFunds): bool
    {
        return $authUser->can('Update:TransferFunds');
    }

    public function delete(AuthUser $authUser, TransferFunds $transferFunds): bool
    {
        return $authUser->can('Delete:TransferFunds');
    }

    public function restore(AuthUser $authUser, TransferFunds $transferFunds): bool
    {
        return $authUser->can('Restore:TransferFunds');
    }

    public function forceDelete(AuthUser $authUser, TransferFunds $transferFunds): bool
    {
        return $authUser->can('ForceDelete:TransferFunds');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TransferFunds');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TransferFunds');
    }

    public function replicate(AuthUser $authUser, TransferFunds $transferFunds): bool
    {
        return $authUser->can('Replicate:TransferFunds');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TransferFunds');
    }

}