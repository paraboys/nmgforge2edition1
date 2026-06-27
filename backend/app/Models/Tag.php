<?php

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'name',
    ];

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class);
    }
}
