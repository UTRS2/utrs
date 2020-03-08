<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
	protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    public function logs()
    {
        return $this->hasOne('App\Log', 'id','logID');
    }
}
