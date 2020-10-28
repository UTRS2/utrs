<?php

namespace App\Models\Old;

use Illuminate\Database\Eloquent\Model;

class Oldappeal extends Model
{
    protected $primaryKey = 'appealID';
    public $timestamps = false;

    public $appends = ['id'];

    public function comments()
    {
        return $this->hasMany(Oldcomment::class, 'appealID','appealID');
    }

    /** hack: add read-only 'id' attribute even thru it's appealID in database. ref #236 */
    public function getIdAttribute()
    {
        return $this->appealID;
    }
}
