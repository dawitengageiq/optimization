<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiBasicAuthorization
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userEmail = $request->getUser();
        $password = $request->getPassword();

        $headers = ['WWW-Authenticate' => 'Basic'];

        $responseMessage = [
            'message' => 'Invalid Credentials',
        ];

        $envAPIUser = config('settings.api_user');
        $envAPIKey = config('settings.api_key');

        if ($userEmail == $envAPIUser && $password == $envAPIKey) {
            return $next($request);
        } else {
            // return response()->json($response, 401);
            return response()->json($responseMessage, 401, $headers);
        }
    }
}
