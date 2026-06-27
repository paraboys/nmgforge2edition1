<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, BelongsToTenant;

    protected $fillable = ['name', 'email', 'password', 'role', 'organization_id'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function ticketsAsRequester()
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    public function ticketsAsAssignee()
    {
        return $this->hasMany(Ticket::class, 'assignee_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'actor_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isAgent(): bool
    {
        return $this->role === UserRole::AGENT;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }
}
