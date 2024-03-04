<?php

namespace App\Helpers\auth;

trait AuthenticatesAndRegistersUsers
{
    use AuthenticatesUsers, CustomRegistersUsers {
        AuthenticatesUsers::redirectPath insteadof CustomRegistersUsers;
    }
}
