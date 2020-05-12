<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AddHstsHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        $time = intval(env('HSTS_MAX_AGE', 0), 10);
        if ($request->isSecure() && $time > 0) {
            $response->header('Strict-Transport-Security', "max-age=$time");
        }

        return $response;
    }
}
