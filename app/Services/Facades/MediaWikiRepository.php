<?php

namespace App\Services\Facades;

use App\Services\MediaWiki\Api\MediaWikiRepository as ActualMediaWikiRepository;
use Illuminate\Support\Facades\Facade;

class MediaWikiRepository extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ActualMediaWikiRepository::class;
    }
}
