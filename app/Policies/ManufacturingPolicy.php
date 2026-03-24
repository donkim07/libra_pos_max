<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Manufacturing;
use Illuminate\Auth\Access\HandlesAuthorization;

class ManufacturingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Manufacturing');
    }

    public function view(AuthUser $authUser, Manufacturing $manufacturing): bool
    {
        return $authUser->can('View:Manufacturing');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Manufacturing');
    }

    public function update(AuthUser $authUser, Manufacturing $manufacturing): bool
    {
        return $authUser->can('Update:Manufacturing');
    }

    public function delete(AuthUser $authUser, Manufacturing $manufacturing): bool
    {
        return $authUser->can('Delete:Manufacturing');
    }

    public function restore(AuthUser $authUser, Manufacturing $manufacturing): bool
    {
        return $authUser->can('Restore:Manufacturing');
    }

    public function forceDelete(AuthUser $authUser, Manufacturing $manufacturing): bool
    {
        return $authUser->can('ForceDelete:Manufacturing');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Manufacturing');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Manufacturing');
    }

    public function replicate(AuthUser $authUser, Manufacturing $manufacturing): bool
    {
        return $authUser->can('Replicate:Manufacturing');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Manufacturing');
    }

}