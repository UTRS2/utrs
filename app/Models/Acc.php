<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appeal;
use App\Models\User;

class Acc extends Model
{
    use HasFactory;

    const QUEUE_PROXY = 5;
    const QUEUE_STEWARD = 6;
    const QUEUE_CHECKUSER = 3;
    const QUEUE_NORMAL = 1;

    //no timestamps
    public $timestamps = false;

    //mass assignable
    protected $fillable = [
        'token',
        'status',
        'appeal_id',
        'acc_id'
    ];

    //relationships
    public function appeal()
    {
        return $this->belongsTo(Appeal::class);
    }

    //transfer to acc
    // send data to ACC via the API
    public static function sendToACC(Appeal $appeal)
    {
        // get the private appeal data with the following fields:
        // ipv4, ipv6, ua, trusted, proxy
        $private = $appeal->privateData()->first();

        // set networkIdentity to an array
        // there will be two ip fields based on the version, sort out which one to use
        $networkIdentity = [];
        // check if the 'ip' field is ipv4 or ipv6 and add it to the networkIdentity array
        if (filter_var($private->ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $networkIdentity[0]['ipv4'] = $private->ipaddress;
        } elseif (filter_var($private->ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $networkIdentity[0]['ipv6'] = $private->ipaddress;
        }
        // add the ua, trusted, and proxy fields to the networkIdentity array
        $networkIdentity[0]['ua'] = $private->ua;
        $networkIdentity[0]['trusted'] = TRUE;
        $networkIdentity[0]['proxy'] = $appeal->proxy;

        if (filter_var($private->ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $networkIdentity[1]['ipv4'] = $appeal->ip;
        } elseif (filter_var($private->ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $networkIdentity[1]['ipv6'] = $appeal->ip;
        }
        $networkIdentity[1]['ua'] = NULL;
        $networkIdentity[1]['trusted'] = FALSE;
        $networkIdentity[1]['proxy'] = NULL;

        // get logs related to the appeal, sort through them, filter any with the action 'responded' or 'comment', replace any reason with a note that private functionary data was involved and can't be transferred if protected > 0, and only take the user_id and the reason fields
        $logs = $appeal->comments()->get()->filter(function ($log) {
            return $log->action === 'responded' || $log->action === 'comment';
        })->map(function ($log) {
            if ($log->protected > 0) {
                $log->reason = 'Private functionary data involved, this comment cannot be made available outside UTRS.';
            }
            if ($log->user_id === 0) {
                return [
                    'username' => 'UTRS System',
                    'reason' => $log->reason
                ];
            }
            elseif ($log->user_id === -1) {
                return [
                    'username' => NULL,
                    'reason' => $log->reason
                ];
            } else {
                return [
                    'username' => User::findOrFail($log->user_id)->username,
                    'reason' => $log->reason
                ];
            }
        });

        // if the wiki is:
        // enwiki, queueId = QUEUE_NORMAL
        // proxy, queueId = QUEUE_PROXY
        // steward, queueId = QUEUE_STEWARD
        // checkuser, queueId = QUEUE_CHECKUSER
        if ($appeal->proxy === TRUE) {
            $queueId = self::QUEUE_PROXY;
        } elseif ($appeal->wiki === 'global') {
            $queueId = self::QUEUE_STEWARD;
        //else if checkuser is in a lowercase version of block reason
        } elseif (strpos(strtolower($appeal->blockreason), 'checkuser') !== FALSE) {
            $queueId = self::QUEUE_CHECKUSER;
        } else {
            $queueId = self::QUEUE_NORMAL;
        }

        // make a json object to send to the ACC API
        $data = [
            'utrsId' => $appeal->id,
            'email' => $appeal->email,
            'emailConfirmed' => $appeal->email_confirmed,
            'networkIdentity' => $networkIdentity,
            'blockInfo' => [
                'admin' => $appeal->blockingadmin,
                'comment' => $appeal->blockreason,
            ],
            'comment' => $logs,
            'queueId' => $queueId,
            'domain' => 'enwiki'
        ];

        //turn data into json
        $json = json_encode($data);

        // if acc is on in an environment variable, send the data to the ACC API
        if (env('ACC_ON')) {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', env('ACC_API_URL'), [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Cache-Control' => 'no-store',
                    'Authorization' => 'Bearer ' . env('ACC_API_KEY'),
                    'X-ACC-API-Version' => '1'
                ],
                'body' => $json
            ]);
            $response = json_decode($response->getBody());
            if ($response->status === 'success') {
                $appeal->status = Appeal::STATUS_ACC;
                $appeal->save();
                // create an acc object
                $acc = new Acc();
                // set the appeal_id to the id of the appeal
                $acc->appeal_id = $appeal->id;
                // get the token from the response
                $acc->token = $response->token;
                // set the status to what the response status is
                $acc->status = $response->status;
                // store the acc_id from the response
                $acc->acc_id = $response->accId;
                // save the acc object
                $acc->save();
            }
            return TRUE;
        } else {
            $appeal->status = Appeal::STATUS_ACC;
            $appeal->save();
            // generate fake acc
            $acc = new Acc();
            $acc->appeal_id = $appeal->id;
            // random token
            $acc->token = bin2hex(random_bytes(16));
            // random acc id 6 numbers
            $acc->acc_id = rand(100000, 999999);
            // open status
            $acc->status = 'open';
            // save the acc object
            $acc->save();

            // dump and die the data if ACC is not on
            dd($json);
        }
    }
}
