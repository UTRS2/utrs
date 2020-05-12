<?php

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string
     */
    protected $proxies = [
        // https://openstack-browser.toolforge.org/project/project-proxy
        '172.16.0.164', // proxy-01.project-proxy.eqiad.wmflabs
        '172.16.0.165', // proxy-02.project-proxy.eqiad.wmflabs
    ];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
