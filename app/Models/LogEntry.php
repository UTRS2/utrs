<?php

namespace App\Models;

use App\MwApi\MwApiUrls;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LogEntry extends Model
{
    const LOG_PROTECTION_NONE = 0; // everyone
    const LOG_PROTECTION_ADMIN = 1; // anybody who can process the appeal, but not the user theirself
    const LOG_PROTECTION_FUNCTIONARY = 2; // functionaries only

    public $timestamps = false;
    protected $guarded = ['id'];

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
        $object = $this->model;

        if (!$object) {
            return null;
        }

        if (!$object->wiki) {
            return null;
        }

        return in_array($object->wiki, MwApiUrls::getSupportedWikis(true))
            ? $object->wiki
            : null;
    }
}
