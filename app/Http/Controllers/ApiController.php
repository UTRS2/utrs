<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appeal;
use App\Models\Acc;
use App\Models\LogEntry;
use App\Models\Apikey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function apiList() {
        // run a can statement to check if the user has the correct permissions
        $this->authorize('viewAny', Apikey::class);
        // get the list of api keys
        $apiKeys = Apikey::all();
        return view('apikey.list', ['apikeys' => $apiKeys]);
    }

    public function create(Request $request) {
        // run a can statement to check if the user has the correct permissions
        $this->authorize('create', Apikey::class);

        // validate the permission is a valid permission
        $request->validate([
            'permission' => 'required|in:admin,acc,public',
            'name' => 'required',
        ]);

        // create a new api key
        $key = new Apikey();
        $key->key = bin2hex(random_bytes(32));
        $key->name = $request->name;
        $key->permission = $request->permission;
        $key->expires_at = $request->expires_at;
        $key->active = true;
        $key->save();
        return redirect()->route('apikey.list');
    }

    public function revoke(Request $request, Apikey $apikey) {
        // run a can statement to check if the user has the correct permissions
        $this->authorize('revoke', $apikey);

        // check if the api key is already inactive, and if so return with an error
        if (!$apikey->active) {
            return redirect()->route('apikey.list')->withErrors(['The API key is already inactive.']);
        }

        // revoke the api key
        $apikey->active = false;
        $apikey->save();

        // log the action
        $log = new LogEntry();
        $log->user_id = Auth::id();
        $log->model_type = "App\Models\Apikey";
        $log->model_id = $apikey->id;
        $log->action = "Revoke API Key";
        $log->reason = NULL;
        $log->ip = $request->ip();
        $log->ua = $request->userAgent();
        $log->save();

        return redirect()->route('apikey.list');
    }

    public function activate(Request $request, Apikey $apikey) {
        // run a can statement to check if the user has the correct permissions
        $this->authorize('revoke', $apikey);
        
        // validate the api key is not already active
        $validator = Validator::make($apikey->toArray(), [
            'active' => 'max:0',
        ]);

        // if no expiration date is provided, return an error
        if ($request->input('expires_at')==NULL) {
            return redirect()->route('apikey.list')->withErrors(['An expiration date is required.']);
        }

        // activate the api key
        $apikey->active = true;
        //set experation date based on input
        $apikey->expires_at = $request->input('expires_at');
        $apikey->save();

        // log the action
        $log = new LogEntry();
        $log->user_id = Auth::id();
        $log->model_type = "App\Models\Apikey";
        $log->model_id = $apikey->id;
        $log->action = "Activate API Key";
        $log->reason = $request->input('expires_at');
        $log->ip = $request->ip();
        $log->ua = $request->userAgent();
        $log->save();
        return redirect()->route('apikey.list');
    }

    public function regenerate(Request $request, Apikey $apikey) {
        // run a can statement to check if the user has the correct permissions
        $this->authorize('revoke', $apikey);

        // regenerate the api key
        $apikey->key = bin2hex(random_bytes(32));
        $apikey->save();

        // log the action
        $log = new LogEntry();
        $log->user_id = Auth::id();
        $log->model_type = "App\Models\Apikey";
        $log->model_id = $apikey->id;
        $log->action = "Regenerate API Key";
        $log->reason = NULL;
        $log->ip = $request->ip();
        $log->ua = $request->userAgent();
        $log->save();
        return redirect()->route('apikey.list');
    }

    public function storeAcc(Request $request): array
    {
        // check the headers for the api key in a Bearer format, and check it against the database
        $key = Apikey::where('key', $request->bearerToken())->first();
        if (!$key || !$key->isActive() || $key->permission !== 'acc') {
            return [
                'status' => 'error',
                'code' => 403,
                'api_error_code' => 1010,
                'message' => 'Invalid API key.'
            ];
        }
        
        // if the request does not have any fields, return an error
        if (!$request->has('utrsId') || !$request->has('token') || !$request->has('status') || !$request->has('accId')) {
            return [
                'status' => 'error',
                'code' => 400,
                'api_error_code' => 1000,
                'message' => 'The request is missing required fields.'
            ];
        }
        try {
            $this->appeal = Appeal::findOrFail($request->utrsId);
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'code' => 404,
                'api_error_code' => 1001,
                'message' => 'No appeal found with that ID.'
            ];
        }
        // if the appeal does not have a corresponding acc, return an error
        if (!$this->appeal->acc_entry) {
            return [
                'status' => 'error',
                'code' => 404,
                'api_error_code' => 1003,
                'message' => 'Our records show this appeal was not transferred to ACC.'
            ];
        }
        // if the token is not the same as the one in the appeal, return an error
        if ($this->acc->token !== $request->token) {
            return [
                'status' => 'error',
                'code' => 403,
                'api_error_code' => 1002,
                'message' => 'The token provided does not match the token that should have been provided.'
            ];
        }

        try {
            $acc = $appeal->getACC();
            $acc->status = $request->status;
            $acc->acc_id = $request->accId;

            if (!$request->closureType && $request->closureType !== "") {
                $acc->result = $request->closureType;
            }
            $acc->save();
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'code' => 500,
                'api_error_code' => 1004,
                'message' => 'Updating the ACC entry failed.'
            ];
        }

        try {
            // log the action with the acc result
            $log = new LogEntry();
            $log->user_id = 0;
            $log->model_type = "App\Models\Appeal";
            $log->model_id = $this->appeal->id;
            $log->action = "ACC Response";
            $log->reason = $request->closureType;
            $log->ip = $request->ip();
            $log->ua = $request->userAgent();
            $log->save();
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'code' => 500,
                'api_error_code' => 1005,
                'message' => 'Logging the action failed.'
            ];
        }


        return [
            'status' => 'OK',
            'error' => null,
            'api_error_code' => 0,
        ];
    }
}
