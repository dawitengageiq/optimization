<?php

namespace App\Commands;

class GetUserActionPermission extends Command
{
    protected $user;

    protected $code;

    /**
     * Create a new command instance.
     */
    public function __construct($user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Execute the command.
     */
    public function handle(): int
    {

        if ($this->user == null || $this->user->role == null) {
            //needs fixing
            return 1;
        }

        $actions = $this->user->role->actions;
        $permitted = 0;

        foreach ($actions as $action) {
            if ($this->code == $action->code) {
                //if found get the permission and stop the finding
                $permitted = $action->pivot->permitted;
                break;
            }
        }

        return $permitted == 1;
    }
}
