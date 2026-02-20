<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apikey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    // no timestamps
    public $timestamps = false;

    //has logs
    public function logs()
    {
        return $this->hasMany(LogEntry::class);
    }

    // ensure an api key is active and has not expired
    public function isActive(): bool
    {
        return $this->active && $this->expires_at > now();
    }
}
