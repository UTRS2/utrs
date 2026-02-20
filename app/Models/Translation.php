<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'appeal_id',
        'log_entries_id',
        'language',
        'translation',
    ];

    // no timestamps
    public $timestamps = false;

    public function appeal()
    {
        return $this->belongsTo(Appeal::class);
    }

    public function logEntry()
    {
        return $this->belongsTo(LogEntry::class);
    }

    public function user_translations()
    {
        // return has one relationship with the user translations
        return $this->hasOne(UserTranslation::class);
    }


}
