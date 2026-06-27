<?php

namespace App\Models;

use App\Enums\ActivityAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['ticket_id', 'actor_id', 'action', 'meta', 'created_at'];

    protected function casts(): array
    {
        return [
            'action' => ActivityAction::class,
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
