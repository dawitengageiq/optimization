<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class UserActionEvent extends Event
{
    use SerializesModels;

    public $logData;

    /**
     * Create a new event instance.
     *
     * UserActionEvent constructor.
     */
    public function __construct(array $logData = [])
    {
        $this->logData = $logData;
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
