<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
	protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'is_protected' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasOne('App\Log', 'id','logID');
    }
}
