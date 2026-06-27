<?php

namespace App\Models\Traits;

use App\Models\Organization;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToOrganization
{
    protected static function bootBelongsToOrganization()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (empty($model->organization_id) && Auth::hasUser()) {
                $model->organization_id = Auth::user()->organization_id;
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
