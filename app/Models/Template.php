<?php

namespace App\Models;

use App\Utils\Logging\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;
    use Loggable;

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
