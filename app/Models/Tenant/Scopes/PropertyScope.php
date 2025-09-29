<?php

namespace App\Models\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class PropertyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply the scope if user is authenticated and property_id is available
        if (auth()->check() && property_id()) {
            $builder->where(function ($query) {
                $query->where('property_id', property_id())
                      ->orWhereNull('property_id'); // Include records with null property_id
            });
        }
    }
}