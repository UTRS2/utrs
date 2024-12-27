<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Appeal;
use App\Models\LogEntry;
use App\Models\Acc;

class AppealResource extends JsonResource
{

    /**
     * File the ACC data into the appeal
     * 
     * 
     */
    public function storeAcc($request): array
    {
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
            $acc->save();
            $acc = $appeal->getACC();
            $acc->status = $request->status;
            $acc->acc_id = $request->accId;

            if (!$request->closureType && $request->closureType !== "") {
                $acc->result = $request->closureType;
            }
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
