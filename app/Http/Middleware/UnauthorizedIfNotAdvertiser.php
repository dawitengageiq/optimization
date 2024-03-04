<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnauthorizedIfNotAdvertiser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): \Symfony\Component\HttpFoundation\Response
    {

        if ($request->user()) {
            //if(!$request->user()->advertiser && !$request->user()->isAdministrator())
            if (! $request->user()->advertiser) {
                //if user is using ajax and it is admin then allow it
                if ($request->ajax() && $request->user()->isAdministrator()) {
                    //return response('Unauthorized.', 401);
                    return $next($request);
                }

                //return response('Unauthorized.', 401);
                return new Response(view('errors.unauthorized'), 401);
            }
        } else {
            //return response('Unauthorized.', 401);
            return new Response(view('errors.unauthorized'), 401);
        }

        return $next($request);
    }
}
