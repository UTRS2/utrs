<?php

namespace Tests\Fakes\MediaWiki;

use App\Services\MediaWiki\Api\MediaWikiApi;
use App\Services\MediaWiki\Api\MediaWikiExtras;
use Mediawiki\Api\MediawikiApi as AddwikiMediaWikiApi;
use Mediawiki\Api\MediawikiFactory;
use RuntimeException;

class FakeMediaWikiApi implements MediaWikiApi
{
    /** @var FakeMediaWikiRepository */
    private $repository;

    /** @var string */
    private $wiki;

    public function __construct(FakeMediaWikiRepository $repository, string $wiki)
    {
        $this->repository = $repository;
        $this->wiki = $wiki;
    }

    public function getRepository(): FakeMediaWikiRepository
    {
        return $this->repository;
    }

    public function getAddWikiMediaWikiApi(): AddwikiMediaWikiApi
    {
        return new FakeMediaWikiApiAddwikiApi($this);
    }

    public function getAddWikiServices(): MediawikiFactory
    {
        return new FakeMediaWikiApiServiceFactory($this);
    }

    public function getMediaWikiExtras(): MediaWikiExtras
    {
        return new FakeMediaWikiExtras($this);
    }

    public function getWikiName(): string
    {
        return $this->wiki;
    }
}
