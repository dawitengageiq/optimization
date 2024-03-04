<?php

namespace App\Helpers\auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

trait CustomRegistersUsers
{
    use RedirectsUsers;

    /**
     * Show the application registration form.
     */
    public function getRegister(): Response
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     */
    public function postRegister(Request $request): Response
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        Auth::login($this->create($request->all()));

        return redirect($this->redirectPath());
    }
}
