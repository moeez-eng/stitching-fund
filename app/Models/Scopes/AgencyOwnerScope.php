<?php

namespace App\Models\Scopes;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class AgencyOwnerScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();
        
        // If there's no authenticated user, don't apply any scope
        if (!$user) {
            return;
        }

        // Check if user has the isAgencyOwner method and if they are an agency owner
        if ($user instanceof \App\Models\User && $user->isAgencyOwner()) {
            return;
        }
        
        // For non-agency owners, apply the scope
        if (property_exists($user, 'id')) {
            $builder->where('user_id', $user->id);
        }
    }
}
