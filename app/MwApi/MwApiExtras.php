<?php

namespace App\MwApi;

use Mediawiki\Api\SimpleRequest;

/**
 * Extra functions that addwiki/mediawiki-api does not provide itself
 */
class MwApiExtras
{
    public static function canEmail($wiki, $username)
    {
        $api = MwApiGetter::getApiForWiki($wiki);
        $response = $api->getRequest(new SimpleRequest(
            'query',
            [
                'list' => 'users',
                'ususers' => $username,
                'usprop' => 'emailable',
            ]
        ));

        return !empty($response['query']['users']) && isset($response['query']['users'][0]['emailable']);
    }

    public static function sendEmail($wiki, $username, $title, $content)
    {
        $api = MwApiGetter::getApiForWiki($wiki);

        $response = $api->postRequest(new SimpleRequest('emailuser', [
            'token' => $api->getToken(),
            'target' => $username,
            'subject' => $title,
            'text' => $content,
        ]));

        // TODO: do something with response?
    }

    public static function getBlockInfo($wiki, $username)
    {
        $key = filter_var($username, FILTER_VALIDATE_IP) === false ? 'bkusers' : 'bkip';

        $api = MwApiGetter::getApiForWiki($wiki);
        $response = $api->getRequest(new SimpleRequest(
            'query',
            [
                'list' => 'blocks',
                $key => $username,
                'bkprop' => 'by|byid|expiry|flags|id|range|reason|restrictions|timestamp|user|userid',
            ]
        ));

        if (empty($response['query']['blocks'])) {
            return null;
        }

        return $response['query']['blocks'][0];
    }
}
