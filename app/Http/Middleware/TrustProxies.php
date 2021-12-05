<?php

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string|null
     */
    protected $proxies = [
        // https://openstack-browser.toolforge.org/project/project-proxy
        '172.16.5.238', // proxy-03.project-proxy.eqiad1.wikimedia.cloud
        '172.16.5.200', // proxy-04.project-proxy.eqiad1.wikimedia.cloud
    ];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
