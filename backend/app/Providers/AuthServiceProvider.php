<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\TicketPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Ticket::class => TicketPolicy::class,
        Comment::class => CommentPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
