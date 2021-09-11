<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wiki extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'is_accepting_appeals' => 'boolean',
    ];

    public function templates()
    {
        return $this->hasMany(Template::class);
    }
}
