<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['organization_id', 'priority', 'response_minutes', 'resolution_minutes'];

    protected function casts(): array
    {
        return [
            'response_minutes' => 'integer',
            'resolution_minutes' => 'integer',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
