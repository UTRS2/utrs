<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PythonJob extends Model
{
    use HasFactory;

    protected $table = 'pythonjob';

    protected $fillable = [
        'appeal_id',
        'request_name',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function appeal()
    {
        return $this->belongsTo(Appeal::class, 'appeal_id', 'id');
    }

    public function getSubmittedAtAttribute($value)
    {
        return $value ? $value->format('Y-m-d H:i:s') : null;
    }
}
