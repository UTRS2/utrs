<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'active' => 'boolean',
    ];

    public function wiki()
    {
        return $this->belongsTo(Wiki::class);
    }
}
