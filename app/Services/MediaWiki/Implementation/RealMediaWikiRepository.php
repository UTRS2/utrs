<?php

namespace App\Services\MediaWiki\Implementation;

use App\Services\MediaWiki\Api\MediaWikiApi;
use App\Services\MediaWiki\Api\MediaWikiRepository;
use App\Services\MediaWiki\Api\WikiAccessChecker;
use App\Services\MediaWiki\Api\WikiPermissionHandler;
use App\Services\MediaWiki\Implementation\Access\GlobalAccessChecker;
use App\Services\MediaWiki\Implementation\Access\LocalAccessChecker;
use App\Models\Wiki;
use Illuminate\Support\Str;

class RealMediaWikiRepository implements MediaWikiRepository
{
    /** @var array [wiki id => {@link RealMediaWikiRepository} */
    private $loadedApis = [];

    /** @var array [wiki id => {@link RealWikiPermissionHandler}] */
    private $loadedPermissionHandlers = [];

    public function getID(string $target) {
        return Wiki::where('database_name',$target)->get()->first()->id;
    }

    public function getCodedName(string $target) {
        return Wiki::where('id',$target)->get()->first()->database_name;
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedTargets($includeGlobal = true): array
    {
        if (!$includeGlobal) {
            return array_keys(config('wikis.wikis'));
        }

        return collect(array_keys(config('wikis.wikis')))
            ->push('global')
            ->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function getTargetProperty(string $target, string $name, $default = null)
    {
        $target = Str::lower($target);
        $prefix = 'wikis.wikis.' . $target . '.';

        if ($target === 'global') {
            $prefix = 'wikis.globalwiki.';
        }

        return config($prefix . $name, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function getApiForTarget(string $target): MediawikiApi
    {
        if (!array_key_exists($target, $this->loadedApis)) {
            $this->loadedApis[$target] = new RealMediaWikiApi($target,
                self::getTargetProperty($target, 'api_url'));
        }

        return $this->loadedApis[$target];
    }

    public function getGlobalApi(): MediaWikiApi
    {
        return $this->getApiForTarget('global');
    }

    public function getWikiPermissionHandler(string $wiki): WikiPermissionHandler
    {
        if (!array_key_exists($wiki, $this->loadedPermissionHandlers)) {
            $this->loadedPermissionHandlers[$wiki] = new RealWikiPermissionHandler(config('wikis.base_permissions'),
                self::getTargetProperty($wiki, 'permission_overrides', []));
        }

        return $this->loadedPermissionHandlers[$wiki];
    }

    public function getWikiAccessChecker(string $wiki): WikiAccessChecker
    {
        if ($wiki === 'global') {
            return new GlobalAccessChecker($this->getGlobalApi());
        }

        return new LocalAccessChecker($this->getApiForTarget($wiki));
    }
}
