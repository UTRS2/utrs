<?php

namespace App\MwApi;

use App\Utils\IPUtils;
use Exception;
use Illuminate\Support\Facades\Log;
use Mediawiki\Api\SimpleRequest;
use RuntimeException;

/**
 * Extra functions that addwiki/mediawiki-api does not provide itself
 */
class MwApiExtras
{
    /**
     * Checks if the user can email
     * 
     * @param  string $wiki - The wiki which the block info will be retrived from
     * @param  string $username - Username to be searched
     * @return boolean - if the user can be emailed
     */
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
    /**
     * Sends an email through the Wiki API
     * 
     * @param  string $wiki - The wiki which the block info will be retrived from
     * @param  string $username - Username to be searched
     * @param  string $title - Subject line for the email
     * @param  string $content - content of the email
     * @throws RuntimeException - if sending the e-mail failed
     */
    public static function sendEmail($wiki, $username, $title, $content)
    {
        $api = MwApiGetter::getApiForWiki($wiki);

        $response = $api->postRequest(new SimpleRequest('emailuser', [
            'token' => $api->getToken(),
            'target' => $username,
            'subject' => $title,
            'text' => $content,
        ]));

        if ($response['emailuser']['result'] !== 'Success') {
            throw new RuntimeException('Failed to send an e-mail: result was ' . $response['emailuser']['result']
                . ': ' . json_encode($response['emailuser']));
        }
    }

    /**
     *
     * Gets the block info from a wiki
     * 
     * @param  string $wiki - The wiki which the block info will be retrived from
     * @param  string $target - Username to be searched
     * @param  int    $appealid - ID of the appeal being queried (for logging)
     * @param  string $key - to allow additional types of blocks (only 3 really exist though: bkusers, bkip, bkids)
     * @return array $response - the block information that comes up
     */
    public static function getBlockInfo($wiki, $target, $appealid, $key = null)
    {
        if (!$appealid) {
            Log::critical("The appeal ID has not been set when calling getBlockInfo() - Terminating - Unknown Source");
            return null;
        }
        if (!$target) {
            Log::critical("The target has not been set when calling getBlockInfo() for appealID #".$appealid." - Terminating");
            return null;
        }

        if (!$key) {
            $key = (filter_var($target, FILTER_VALIDATE_IP) === false && !IPUtils::isIpRange($target)) ? 'bkusers' : 'bkip';
        }

        $api = MwApiGetter::getApiForWiki($wiki);
        try {
            $response = $api->getRequest(new SimpleRequest(
                'query',
                [
                    'list' => 'blocks',
                    $key => $target,
                    'bkprop' => 'by|byid|expiry|flags|id|range|reason|restrictions|timestamp|user|userid',
                ]
            ));
        } catch (Exception $e) {
            Log::error("MediaWiki API Failure: ".$e->getMessage()." on appealID #".$appealid);
            return null;
        }    

        if (empty($response['query']['blocks'])) {
            return null;
        }

        return $response['query']['blocks'][0];
    }

    /**
     * Gets the info of global blocks
     * 
     * @param  string $target - Username to be searched
     * @param  int    $appealid - ID of the appeal being queried (for logging)
     * @return array - information about the block
     */
    public static function getGlobalBlockInfo($target, $appealid)
    {
        if (!$appealid) {
            Log::critical("The appeal ID has not been set when calling getGlobalBlockInfo() - Terminating - Unknown Source");
            return null;
        }
        if (!$target) {
            Log::critical("The target has not been set when calling getGlobalBlockInfo() for appealID #".$appealid." - Terminating");
            return null;
        }

        $api = MwApiGetter::getApiForWiki('global');

        if (filter_var($target, FILTER_VALIDATE_IP) !== false || IPUtils::isIpRange($target)) {
            // is ip

            try {
                $response = $api->getRequest(new SimpleRequest(
                    'query',
                    [
                        'list' => 'globalblocks',
                        'bgip' => $target,
                        'bkprop' => 'address|by|expiry|id|range|reason|timestamp',
                    ]
                ));
            } catch (Exception $e) {
                Log::error("MediaWiki API Failure: ".$e->getMessage()." on appealID #".$appealid);
                return null;
            }

            if (empty($response['query']['globalblocks'])) {
                return null;
            }

            return $response['query']['globalblocks'][0];
        }

        $entries = MwApiGetter::getServicesForWiki('global')->newLogListGetter()
            ->getLogList([
                'letype' => 'globalauth',
                'letitle' => 'User:' . $target . '@global'
            ]);

        $entry = $entries->getLatest();

        if (!$entry || $entry->getDetails()['params'][0] !== 'locked') {
            return null;
        }

        // this looks something like details of other types
        return [
            'by' => $entry->getUser(),
            'user' => $target,
            'timestamp' => $entry->getTimestamp(),
            'reason' => $entry->getComment(),
        ];
    }
}
