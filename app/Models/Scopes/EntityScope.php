<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EntityScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check()) {
            $builder->whereRaw('1 = 0');
            return;
        }

        $user = auth()->user();
        if (!$user->hasRole('super-admin')) {
            $builder->where($model->getTable() . '.entity_id', $user->entity_id);
        }
    }
}
