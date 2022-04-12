<?php

namespace App\Services\MediaWiki\Implementation;

use App\Services\MediaWiki\Api\Data\Block;
use App\Services\MediaWiki\Api\MediaWikiExtras;
use App\Services\MediaWiki\Implementation\Data\RealBlock;
use App\Utils\IPUtils;
use Exception;
use Illuminate\Support\Facades\Log;
use Mediawiki\Api\SimpleRequest;

class RealMediaWikiExtras implements MediaWikiExtras
{
    /** @var RealMediaWikiApi */
    private $api;

    public function __construct(RealMediaWikiApi $api)
    {
        $this->api = $api;
    }

    private function getApi()
    {
        return $this->api->getAddWikiMediaWikiApi();
    }

    private function getServices()
    {
        return $this->api->getAddWikiServices();
    }

    public function canEmail(string $username): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        $response = $this->getApi()->getRequest(new SimpleRequest(
            'query',
            [
                'list' => 'users',
                'ususers' => $username,
                'usprop' => 'emailable',
            ]
        ));

        return !empty($response['query']['users']) && isset($response['query']['users'][0]['emailable']);
    }

    public function sendEmail(string $username, string $title, string $content): bool
    {
        $this->api->login();
        $response = $this->getApi()->postRequest(new SimpleRequest('emailuser', [
            'token' => $this->getApi()->getToken(),
            'target' => $username,
            'subject' => $title,
            'text' => $content,
        ]));

        return $response['emailuser']['result'] === 'Success';
    }

    public function getBlockInfo(string $target, int $appealId = -1, string $searchKey = null): ?Block
    {
        if (!$target) {
            Log::critical("The target has not been set when calling getBlockInfo() for appealID #" . $appealId . " - terminating");
            return null;
        }

        if (!$searchKey) {
            $searchKey = (!IPUtils::isIp($target) && !IPUtils::isIpRange($target)) ? 'bkusers' : 'bkip';
        }

        try {
            $response = $this->getApi()->getRequest(new SimpleRequest(
                'query',
                [
                    'list'     => 'blocks',
                    $searchKey => $target,
                    'bkprop'   => 'by|byid|expiry|flags|id|range|reason|restrictions|timestamp|user|userid',
                ]
            ));
        } catch (Exception $e) {
            Log::error("MediaWiki API Failure: " . $e->getMessage() . " on appealID #". $appealId);
            return null;
            //Temp comment this out to see if we can handle with the return null above
            //throw $e;
        }

        if (empty($response['query']['blocks'])) {
            return null;
        }

        $blockData = $response['query']['blocks'][0];
        return RealBlock::fromArray($blockData);
    }

    public function getGlobalBlockInfo(string $target, int $appealId = -1): ?Block
    {
        if (!$target) {
            Log::critical("The target has not been set when calling getGlobalBlockInfo() for appealID #" . $appealId.  " - Terminating");
            return null;
        }

        if (IPUtils::isIp($target) || IPUtils::isIpRange($target)) {
            // is ip

            try {
                $response = $this->getApi()->getRequest(new SimpleRequest(
                    'query',
                    [
                        'list' => 'globalblocks',
                        'bgip' => $target,
                        'bkprop' => 'address|by|expiry|id|range|reason|timestamp',
                    ]
                ));
            } catch (Exception $e) {
                Log::error("MediaWiki API Failure: ".$e->getMessage()." on appealID #" . $appealId);
                throw $e;
            }

            if (empty($response['query']['globalblocks'])) {
                return null;
            }

            return $response['query']['globalblocks'][0];
        }

        try {
            $entries = $this->getServices()->newLogListGetter()
                ->getLogList([
                    'letype' => 'globalauth',
                    'letitle' => 'User:' . $target . '@global'
                ]);
        } catch (Exception $e) {
            Log::error("MediaWiki API Failure: ".$e->getMessage()." on appealID #" . $appealId);
            throw $e;
        }

        $entry = $entries->getLatest();

        if (!$entry || $entry->getDetails()['params'][0] !== 'locked') {
            return null;
        }

        return new RealBlock(
            $entry->getUser(),
            $target,
            $entry->getComment(),
            $entry->getTimestamp(),
        );
    }

    public function getGlobalGroupMembership(string $userName): array
    {
        $response = $this->getApi()->getRequest(new SimpleRequest(
            'query',
            [
                'list' => 'globalallusers',
                'agufrom' => $userName,
                'aguto' => $userName,
                'aguprop' => 'groups',
            ]
        ));

        if (empty($response['query']['globalallusers']) || !isset($response['query']['globalallusers'][0]['groups'])) {
            return [];
        }

        return $response['query']['globalallusers'][0]['groups'];
    }
}
