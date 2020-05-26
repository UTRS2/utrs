<?php

namespace App\OAuth;

use GuzzleHttp\Psr7\Uri;
use League\OAuth1\Client\Signature\HmacSha1Signature;

class WikiHmacSha1Signature extends HmacSha1Signature
{
    /**
     * {@inheritDoc}
     */
    // Overriding this is a hack, and I don't like it. It's needed to include port in OAuth signature uri :/
    protected function baseString(Uri $url, $method = 'POST', array $parameters = array())
    {
        $baseString = rawurlencode($method).'&';

        $schemeHostPath = Uri::fromParts(array(
            'scheme' => $url->getScheme(),
            'host' => $url->getHost(),
            'path' => $url->getPath(),
            'port' => $url->getPort(),
        ));

        $baseString .= rawurlencode($schemeHostPath).'&';

        $data = array();
        parse_str($url->getQuery(), $query);
        $data = array_merge($query, $parameters);

        // normalize data key/values
        array_walk_recursive($data, function (&$key, &$value) {
            $key   = rawurlencode(rawurldecode($key));
            $value = rawurlencode(rawurldecode($value));
        });
        ksort($data);

        $baseString .= $this->queryStringFromData($data);

        return $baseString;
    }
}