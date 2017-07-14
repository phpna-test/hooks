<?php

namespace Gkr\Hooks\Deploy;

use Closure;

class RequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->isJson() && $request->header('content-type') != 'application/x-www-form-urlencoded'){
            abort(403, 'Request content-type of hooks should be json or form!');
        }
        return $next($request);
    }
}
