<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\TaskCompleted::class => [
            \App\Listeners\SendTaskCompletedNotification::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
