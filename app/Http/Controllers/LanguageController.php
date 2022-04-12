<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Session;

class LanguageController extends Controller
{
	public function change($lang) {
		if (!in_array($lang,config('translation.target_locales'))) {
			$lang = 'en';
		}
		App::setLocale($lang);
		Session::put('locale', $lang);
		return redirect()->route('home');
	}
}