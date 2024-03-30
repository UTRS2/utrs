<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appeal;
use App\Models\LogEntry;
use App\Models\User;

class EmailBan extends Model
{
    use HasFactory;

    // set the table name
    protected $table = 'emails';

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    // set the 'linkedappeals' attribute to return the linked appeals with a many-to-one relationship  
    public function linkedappeals()
    {
        // if the linkedappeals attribute is not null, return the linked appeals
        if (isset($this->linkedappeals)) {
            return Appeal::where('id', $this->linkedappeals)->get();
        } else {
            return [];
        }
    }

    // convert any attribute to a human readable format from a datetime
    public function humanFormat($value)
    {
        return date('d/m/Y H:i:s', strtotime($value));
    }

    public function logEmailBan(User $user, EmailBan $ban, $message = '', $reason = '') {
        //if message is blank, throw an error
        if ($message == '') {
            throw new \Exception('Log message cannot be blank');
        }
        $log = new LogEntry();
        $log->user_id = $user->id;
        $log->model_type = EmailBan::class;
        $log->model_id = $ban->id;
        $log->action = 'update emailban';
        $log->reason = $ban->email . $message . " " . $reason;
        $log->save();
    }



    
}
