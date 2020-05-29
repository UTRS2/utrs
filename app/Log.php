<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    const LOG_PROTECTION_NONE = 0; // everyone
    const LOG_PROTECTION_ADMIN = 2; // anybody who can process the appeal, but not the user theirself
    const LOG_PROTECTION_FUNCTIONARY = 1; // functionaries only

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
