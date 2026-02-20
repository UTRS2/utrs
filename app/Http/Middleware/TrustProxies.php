<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
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
        '172.16.19.232',          // proxy-5.project-proxy.eqiad1.wikimedia.cloud
        '2a02:ec80:a000:1::2f3',  // proxy-5.project-proxy.eqiad1.wikimedia.cloud
        '172.16.17.55',           // proxy-6.project-proxy.eqiad1.wikimedia.cloud
        '2a02:ec80:a000:1::31d',  // proxy-6.project-proxy.eqiad1.wikimedia.cloud
    ];

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
