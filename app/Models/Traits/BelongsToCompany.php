<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

// this will automatically scope eloquent queries to the authenticated user's company
trait BelongsToCompany
{
    // boot the trait
    protected static function bootBelongsToCompany(): void
    {
        // for reading
        static::addGlobalScope('company', function (Builder $builder) {
            // only apply the scope if the user is authenticated
            if (Auth::guard('sanctum')->check())
            {
                $builder->where(
                    $builder->getModel()->getTable() . '.company_id',
                    Auth::guard('sanctum')->user()->company_id
                );
            }
        });

        // for writing in the database
        static::creating(function (Model $model) {
            // automatically set the company id from the authenticated user
            if (Auth::guard('sanctum')->check() && empty($model->company_id)) {
                $model->company_id = Auth::guard('sanctum')->user()->company_id;
            }        
        });
    }
}
