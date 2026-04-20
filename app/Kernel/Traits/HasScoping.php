<?php

namespace App\Kernel\Traits;

use App\Kernel\Scoping\ScopeResolver;
use Illuminate\Database\Eloquent\Builder;

trait HasScoping
{
    public static function bootHasScoping(): void
    {
        static::addGlobalScope('scoping', function (Builder $builder) {
            // Prevent infinite recursion during user retrieval
            if ($builder->getModel() instanceof \App\Models\User) {
                return;
            }

            if (auth()->check()) {
                ScopeResolver::apply($builder, auth()->user());
            }
        });

        static::creating(function ($model) {
            if (auth()->check()) {
                $user = auth()->user();
                if (empty($model->entity_id) && isset($user->entity_id)) {
                    $model->entity_id = $user->entity_id;
                }
                if (empty($model->branch_id) && isset($user->branch_id)) {
                    $model->branch_id = $user->branch_id;
                }
            }
        });
    }
}
