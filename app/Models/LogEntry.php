<?php

namespace App\Models;

use App\Services\Facades\MediaWikiRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LogEntry extends Model
{
    use HasFactory;
    
    const LOG_PROTECTION_NONE = 0; // everyone
    const LOG_PROTECTION_ADMIN = 1; // anybody who can process the appeal, but not the user theirself
    const LOG_PROTECTION_FUNCTIONARY = 2; // functionaries only

    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @deprecated Use {@link LogEntry::user()} instead
     */
    public function userObject()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function model()
    {
        return $this->morphTo();
    }

    /**
     * This is a somewhat hacky method to try to guess what wiki is this log entry associated with
     * @return string|null
     */
    public function tryFigureAssociatedWiki()
    {
        if (!$this->model) {
            return null;
        }

        if (!$this->model->wiki) {
            return null;
        }

        return in_array($this->model->wiki, MediaWikiRepository::getSupportedTargets())
            ? $this->model->wiki
            : null;
    }

    public function translations()
    {
        return $this->hasMany(Translation::class, 'log_entries_id');
    }
}
