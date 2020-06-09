<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    const LOG_PROTECTION_NONE = 0; // everyone
    const LOG_PROTECTION_ADMIN = 1; // anybody who can process the appeal, but not the user theirself
    const LOG_PROTECTION_FUNCTIONARY = 2; // functionaries only

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    // ideally this would be named user and the field would be named user_id per laravel norms
    public function userObject()
    {
        return $this->belongsTo(User::class, 'user');
    }
}
