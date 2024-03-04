<?php

namespace App\Helpers\auth;

trait RedirectsUsers
{
    /**
     * Get the post register / login redirect path.
     */
    public function redirectPath(): string
    {
        if (property_exists($this, 'redirectPath')) {
            return $this->redirectPath;
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }
}
