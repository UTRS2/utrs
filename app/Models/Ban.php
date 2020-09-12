<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        return $this->hasOne('App\Models\LogEntry', 'id','logID');
    }

    public function scopeActive(Builder $query)
    {
        // this treats bans whose expire before start of 2000 as indefinite because no real ban will expire before that
        // and that's simpler than comparing it to a specific point because timezones
        return $query
            ->where('expiry', '>=', now())
            ->orWhere('expiry', '<=', '2000-01-01 00:00:00');
    }
}
