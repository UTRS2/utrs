<?php

namespace App\Http\Controllers\Appeal;

use App\Http\Controllers\Controller;
use App\Models\Wiki;

class PublicAppealCreateController extends Controller
{
    private function createWikiDropdown()
    {
        return Wiki::where('is_accepting_appeals', true)
            ->get()
            ->mapWithKeys(function (Wiki $wiki) {
                return [$wiki->id => $wiki->display_name];
            });
    }

    public function showIpForm()
    {
        return view(
            'appeals.public.makeappeal.ip',
            ['wikis' => $this->createWikiDropdown()]
        );
    }

    public function showAccountForm()
    {
        return view(
            'appeals.public.makeappeal.account',
            ['wikis' => $this->createWikiDropdown()]
        );
    }
}
