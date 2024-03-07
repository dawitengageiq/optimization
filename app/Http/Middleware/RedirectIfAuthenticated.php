<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->auth->check()) {
            if ($request->user()->isAdministrator()) {
                return redirect()->to('/admin');
            } elseif ($request->user()->affiliate) {
                return redirect()->to('/affiliate/dashboard');
            } else {
                return redirect()->to('/advertiser/dashboard');
            }
        }

        return $next($request);
    }
}
