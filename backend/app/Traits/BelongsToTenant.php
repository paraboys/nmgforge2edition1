<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            if (auth()->check() && empty($model->organization_id)) {
                $model->organization_id = auth()->user()->organization_id;
            }
        });
    }
}
