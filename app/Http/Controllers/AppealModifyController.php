<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Appeal;

class AppealModifyController extends Controller
{
    public function changeip($hash) {
    	dd($hash);
    	$appeal = Appeal::where('appealsecretkey','=',$hash)->firstOrFail();
    	return view('appeals.fixip', ['appeal'=>$appeal]);
    }
}
