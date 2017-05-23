<?php

namespace Minion\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mMinionings for the Application.
     *
     * @var array
     */
    protected $listen = [
        // Post Model
        'Minion\Events\PostCreated' => [
            'Minion\Listeners\PostCreatedListener',
        ],
        'Minion\Events\PostUpdated' => [
            'Minion\Listeners\PostUpdatedListener',
        ],
        'Minion\Events\PostDeleted' => [
            'Minion\Listeners\PostDeletedListener',
        ],

        // User Model
        'Minion\Events\UserCreated' => [
            'Minion\Listeners\UserCreatedListener',
        ],
        'Minion\Events\UserUpdated' => [
            'Minion\Listeners\UserUpdatedListener',
        ],
        'Minion\Events\UserDeleted' => [
            'Minion\Listeners\UserDeletedListener',
        ],
    ];

    /**
     * Register any events for your Application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
