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

        $time = config('app.hsts_time', 0);
        if ($request->isSecure() && $time > 0) {
            $response->header('Strict-Transport-Security', "max-age=$time");
        }

        return $response;
    }
}
