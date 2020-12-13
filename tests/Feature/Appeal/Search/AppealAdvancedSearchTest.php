<?php

namespace Tests\Feature\Appeal\Search;

use App\Models\Appeal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\TestHasUsers;

/**
 * @covers \App\Http\Controllers\Appeal\AppealAdvancedSearchController
 */
class AppealAdvancedSearchTest extends TestCase
{
    use TestHasUsers;
    use RefreshDatabase;

    public function isFoundWithRequest(Appeal $appeal, array $data = [], ?User $user = null): bool
    {
        if (!$user) {
            $user = $this->getUser();
        }

        if (!array_key_exists('dosearch', $data)) {
            $data['dosearch'] = '1';
        }

        $resultHtml = $this
            ->actingAs($user)
            ->call('GET', route('appeal.search.advanced'), $data)
            ->getContent();

        return Str::contains($resultHtml, '#' . $appeal->id);
    }

    public function testItFiltersAppealByStatus()
    {
        $appeal = Appeal::factory()->create([ 'wiki' => 'enwiki', 'status' => Appeal::STATUS_OPEN, ]);
        $invalidAppeal = Appeal::factory()->create([ 'wiki' => 'enwiki', 'status' => Appeal::STATUS_INVALID ]);

        $this->assertTrue($this->isFoundWithRequest($appeal, [
            'status_OPEN' => '1',
            'wiki_enwiki' => '1',
        ]), 'should find when filters are exact');

        $this->assertTrue($this->isFoundWithRequest($appeal, [
            'status_OPEN' => '1',
            'status_ACCEPT' => '1',
            'wiki_enwiki' => '1',
            'wiki_ptwiki' => '1',
        ]), 'should find when filters have multiple choises');

        $this->assertFalse($this->isFoundWithRequest($appeal, [
            'status_ACCEPT' => '1',
            'wiki_enwiki' => '1',
        ]), 'should not find when filters do not contain current status');


        $this->assertFalse($this->isFoundWithRequest($invalidAppeal, [
            'status_INVALID' => '1',
            'wiki_enwiki' => '1',
        ]), 'should not find when appeal has a non-public status');
    }

    public function testItDoesntSearchWikisThatTheUserCantSee()
    {
        $appeal = Appeal::factory()->create([ 'wiki' => 'ptwiki', 'status' => Appeal::STATUS_OPEN, ]);

        $this->assertFalse($this->isFoundWithRequest($appeal, [
            'status_OPEN' => '1',
            'wiki_ptwiki' => '1',
        ]), 'should not find when filters are exact');

        $this->assertFalse($this->isFoundWithRequest($appeal, [
            'status_OPEN' => '1',
            'status_ACCEPT' => '1',
            'wiki_enwiki' => '1',
            'wiki_ptwiki' => '1',
        ]), 'should not find when filters have multiple choices');
    }

    public function testItFiltersAppealsByHandlingAdmin()
    {
        $handlingAdmin = $this->getUser();
        $appeal = Appeal::factory()->create([ 'wiki' => 'enwiki', 'handlingadmin' => $handlingAdmin ]);

        $this->assertTrue($this->isFoundWithRequest($appeal, [
            'status_OPEN' => '1',
            'wiki_enwiki' => '1',
            'handlingadmin' => $handlingAdmin->username,
        ]), 'should find when filters are exact');

        $this->assertFalse($this->isFoundWithRequest($appeal, [
            'status_OPEN' => '1',
            'wiki_enwiki' => '1',
            'handlingadmin' => $handlingAdmin->username . 'asd',
        ]), 'should find when handling admin filter has a typo');

        $this->assertTrue($this->isFoundWithRequest($appeal, [
            'status_OPEN' => '1',
            'wiki_enwiki' => '1',
        ]), 'should find when handling admin filter is not set');

        $this->assertFalse($this->isFoundWithRequest($appeal, [
            'status_OPEN' => '1',
            'wiki_enwiki' => '1',
            'handlingadmin_none' => '1',
        ]), 'should not find when filtering for no handling admin');
    }
}
