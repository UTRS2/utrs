<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    // ideally this would be named user and the field would be named user_id per laravel norms
    public function userObject()
    {
        return $this->belongsTo(User::class, 'user');
    }
}
