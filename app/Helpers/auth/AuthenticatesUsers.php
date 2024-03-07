<?php

namespace App\Helpers\auth;

use App\User;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

trait AuthenticatesUsers
{
    use RedirectsUsers;

    /**
     * Show the application login form.
     */
    public function getLogin()
    {
        if (view()->exists('auth.authenticate')) {
            return view('auth.authenticate');
        }

        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            $this->loginUsername() => 'required', 'password' => 'required',
        ]);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();
        //
        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->getCredentials($request);

        $errorMessage = $this->getFailedLoginMessage();

        // Log::info($credentials);

        //validate the email
        $validator = Validator::make($credentials, ['email' => 'email'], ['email' => 'Please enter a valid email address!']);

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->all();
        } elseif (! User::where('email', '=', $credentials['email'])->exists()) {
            $errorMessage = 'Sorry, we don\'t recognize that email.';
        } elseif (Auth::attempt($credentials, $request->has('remember'))) {
            return $this->handleUserWasAuthenticated($request, $throttles);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if ($throttles) {
            $this->incrementLoginAttempts($request);
        }

        return redirect()->to($this->loginPath())
            ->withInput($request->only($this->loginUsername(), 'remember'))
            ->withErrors([
                //$this->loginUsername() => $this->getFailedLoginMessage(),
                $this->loginUsername() => $errorMessage,
            ]);
    }

    /**
     * Send the response after the user was authenticated.
     */
    protected function handleUserWasAuthenticated(Request $request, bool $throttles)
    {
        if ($throttles) {
            $this->clearLoginAttempts($request);
        }

        if (method_exists($this, 'authenticated')) {
            return $this->authenticated($request, Auth::user());
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the needed authorization credentials from the request.
     */
    protected function getCredentials(Request $request): array
    {
        return $request->only($this->loginUsername(), 'password');
    }

    /**
     * Get the failed login message.
     */
    protected function getFailedLoginMessage(): string
    {
        return Lang::has('auth.failed')
            ? Lang::get('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Log the user out of the application.
     */
    public function getLogout()
    {
        Auth::logout();

        return redirect()->to(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
        //This is to force redirect to previous page and will eventually redirect to login page in order to prevent the cached internal pages to be viewed when user clicks back after logout.
        //return redirect()->back();
    }

    /**
     * Get the path to the login route.
     */
    public function loginPath(): string
    {
        return property_exists($this, 'loginPath') ? $this->loginPath : '/auth/login';
        //return property_exists($this, 'loginPath') ? $this->loginPath : '/login';
    }

    /**
     * Get the login username to be used by the controller.
     */
    public function loginUsername(): string
    {
        return property_exists($this, 'username') ? $this->username : 'email';
    }

    /**
     * Determine if the class is using the ThrottlesLogins trait.
     */
    protected function isUsingThrottlesLoginsTrait(): bool
    {
        return in_array(
            ThrottlesLogins::class, class_uses_recursive(get_class($this))
        );
    }
}
