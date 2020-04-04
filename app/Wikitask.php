<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wikitask extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
