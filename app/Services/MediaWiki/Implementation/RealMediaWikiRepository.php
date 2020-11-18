<?php

namespace App\Services\MediaWiki\Implementation;

use App\Services\MediaWiki\Api\MediaWikiApi;
use App\Services\MediaWiki\Api\MediaWikiRepository;
use Illuminate\Support\Str;

class RealMediaWikiRepository implements MediaWikiRepository
{
    private $loadedApis = [];

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

        if ($target === 'global' || $target === '*') {
            $prefix = 'wikis.globalwiki.';
        }

        return config($prefix . $name, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function getApiForTarget(string $target): MediawikiApi
    {
        if (!in_array($target, $this->loadedApis)) {
            $this->loadedApis[$target] = new RealMediaWikiApi(self::getTargetProperty($target, 'api_url'));
        }

        return $this->loadedApis[$target];
    }

    public function getGlobalApi(): MediaWikiApi
    {
        return $this->getApiForTarget('global');
    }

    /**
     * {@inheritDoc}
     */
    public function getWikiDropdown(): array
    {
        return collect($this->getSupportedTargets())
            ->mapWithKeys(function ($wiki) {
                return [$wiki => $this->getTargetProperty($wiki, 'name')];
            })
            ->toArray();
    }
}
