<?php namespace App\Http\Middleware;

use Closure;

class AddHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Accept-CH', 'sec-ch-ua-full-version-list, Sec-CH-UA-Platform-Version, Sec-CH-UA-Platform, Sec-CH-UA-Arch, Sec-CH-UA-Model, Sec-CH-UA-Mobile, Device-Memory, Sec-CH-UA-Bitness, Sec-CH-Viewport-Height, Sec-CH-Viewport-Width');

        return $response;
    }
}