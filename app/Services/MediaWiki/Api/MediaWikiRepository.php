<?php

namespace App\Services\MediaWiki\Api;

/**
 * A MediaWikiRepository provides access to multiple {@link MediaWikiApi}s.
 */
interface MediaWikiRepository
{
    /**
     * @param bool $includeGlobal if false, "global" will not be included even if it is supported
     * @return string[] List of targets this repository can access
     */
    public function getSupportedTargets($includeGlobal = true): array;

    /**
     * Retrieves the given property for the given target, or $default if none available.
     * @param string $target Target wiki to get property for
     * @param string $name Property to get
     * @param null $default Value to get if no property is available for the given targets
     * @return mixed Property if present, $default otherwise
     */
    public function getTargetProperty(string $target, string $name, $default = null);

    /**
     * Provides a {@link MediaWikiApi} object for the given target
     * @param string $target Target to get api for
     * @return MediaWikiApi the MediaWikiApi object
     */
    public function getApiForTarget(string $target): MediaWikiApi;

    /**
     * Provides a {@link MediaWikiApi} object for the global wiki
     * @return MediaWikiApi the MediaWikiApi object
     */
    public function getGlobalApi(): MediaWikiApi;

    /**
     * Provides an array that can be used to build a dropdown for all available wikis.
     * @return array Array with [dbName => Human readable name] pairs for all available wikis
     */
    public function getWikiDropdown(): array;
}
