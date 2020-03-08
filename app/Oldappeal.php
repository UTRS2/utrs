<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Oldappeal extends Model
{
    protected $primaryKey = 'appealID';
    public $timestamps = false;

    public function comments()
    {
        return $this->hasMany('App\Oldcomment', 'appealID','appealID');
    }
}
