<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appeal;
use App\Models\Translation;
use App\Models\LogEntry;

class TranslateController extends Controller
{
    // create a new translation for the appeal and store it in the database
    public function activate(Request $request, Appeal $appeal, $logid)
    {
        abort_unless(Auth::check(), 403, 'No logged in user');

        // if no appeal, return 404
        if ($appeal == null) {
            abort(404, 'Appeal not found');
        }

        // if log id from request is null, set it to 0
        if ($logid == null) {
            $logid = 0;
        }

        $requestlang = Auth::user()->default_translation_language;
        // check that the user has a default translation language and if not return to the settings page with an error noting that the user needs to set a default translation language
        if (!$requestlang) {
            return redirect()->route('admin.users.view', Auth::user())->with('error', 'You need to set a default translation language before you can translate appeals.');
        }

        // validate the language code in the user's preferences is a valid language code per the env file
        $languages = env('DEEPL_LANGUAGE_CODES');
        $languages = explode(',', $languages);
        
        if (!in_array($requestlang, $languages)) {
            return redirect()->route('admin.users.view', Auth::user())->with('error', 'Invalid language code');
        }

        // ensure the user has permission to view the appeal
        $this->authorize('view', $appeal);

        //ensure the log entry exists
        if ($logid != 0) {
            $logEntry = LogEntry::findOrFail($logid);
            $translateString = $logEntry->reason;
        }
        else {
            $translateString = $appeal->appealtext;
        }
        
        // ensure the appeal doesn't already have a translation in the language based on the log id
        // if one exists, return to the appeal without a message
        if (Translation::where('appeal_id', $request->appeal_id)->where('log_entries_id', $request->log_entries_id)->where('language', $request->language)->exists()) {
            return redirect()->route('appeal.view', ['id' => $request->appeal_id]);
        }

        // pull the auth key for deepl from the config
        $authKey = env('DEEPL_AUTH_KEY');

        // if the auth key is not set, return an 503 page
        if (!$authKey) {
            abort(503, 'No DeepL auth key set in the config');
        }

        $translator = new \DeepL\Translator($authKey);
        $result = $translator->translateText($translateString, null, $requestlang);

        // create a new translation
        $translation = new Translation();
        $translation->appeal_id = $appeal->id;
        $translation->log_entries_id = $logid;
        $translation->language = $requestlang;
        $translation->translation = $result->text;
        $translation->save();

        // return to the appeal
        return redirect()->route('appeal.view', ['id' => $appeal->id]);
    }
}
