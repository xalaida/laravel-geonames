<?php

namespace App\Nova;

use Illuminate\Http\Request;

/**
 * @mixin Resource
 */
trait ReadOnlyResource
{
    /**
     * Determine if the current user can create new resources.
     */
    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    /**
     * Determine if the current user can update the given resource.
     */
    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    /**
     * Determine if the current user can delete the given resource.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    /**
     * Determine if the current user can replicate the given resource.
     */
    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }
}
