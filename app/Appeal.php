<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Appeal extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $attributes = [
        'privacylevel' => 0,
        'blockfound' => 0
    ];

    public function comments()
    {
        return $this->hasMany('App\Log', 'referenceobject','id')
            ->where('objecttype', 'appeal');
    }

    public function handlingAdminObject()
    {
        return $this->belongsTo(User::class, 'handlingadmin', 'id');
    }
}
