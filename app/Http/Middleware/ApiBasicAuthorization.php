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

        $envAPIUser = env('API_USER', 'api@engageiq.com');
        $envAPIKey = env('API_KEY', '00b462d748d32231540d3dee001dbbadec723a56+e67f380c13a5664c91d48f38d822b3272f2c62a2');

        if ($userEmail == $envAPIUser && $password == $envAPIKey) {
            return $next($request);
        } else {
            // return response()->json($response, 401);
            return response()->json($responseMessage, 401, $headers);
        }
    }
}
