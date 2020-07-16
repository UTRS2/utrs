<?php

namespace App\Http\Controllers\Appeal;

use App\Appeal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublicAccReferralController extends Controller
{
    public function showReferForm(Request $request, string $key)
    {
        $appeal = Appeal::where('appealsecretkey', $key)
            ->where('status', Appeal::STATUS_REFER_ACC)
            ->firstOrFail();

        return view('appeals.public.acc.form', ['appeal' => $appeal]);
    }

    public function processForm(Request $request)
    {
        $appeal = Appeal::where('appealsecretkey', $request->input('secret_key'))
            ->where('status', Appeal::STATUS_REFER_ACC)
            ->firstOrFail();

    }
}
