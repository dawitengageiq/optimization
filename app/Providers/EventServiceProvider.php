<?php

namespace App\Providers;

use App\Events\UserActionEvent;
use App\User;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\UserActionEvent::class => [
            \App\Listeners\UserActionListener::class,
        ],
    ];

    /**
     * Register any other events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Fired on each authentication attempt...
        Event::listen('auth.attempt', function ($credentials, $remember, $login) {

            // get the email and query the user
            $email = $credentials['email'];
            $user = User::where('email', '=', $email)->first();

            event(new UserActionEvent([
                'section_id' => null,
                'sub_section_id' => null,
                'user_id' => $user->id,
                'change_severity' => 1,
                'summary' => "$user->first_name attempted to login with $email.",
                'old_value' => null,
                'new_value' => null,
            ]));
        });

        // Fired on successful logins...
        Event::listen('auth.login', function ($user, $remember) {
            event(new UserActionEvent([
                'section_id' => null,
                'sub_section_id' => null,
                'user_id' => $user->id,
                'change_severity' => 1,
                'summary' => "$user->first_name logged in with $user->email.",
                'old_value' => null,
                'new_value' => null,
            ]));
        });

        // Fired on logouts...
        Event::listen('auth.logout', function ($user) {
            event(new UserActionEvent([
                'section_id' => null,
                'sub_section_id' => null,
                'id' => $user->id,
                'change_severity' => 1,
                'summary' => $user->first_name.' logged out',
                'old_value' => null,
                'new_value' => null,
            ]));
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
