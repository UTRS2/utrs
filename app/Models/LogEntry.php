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
     * This is a hacky method to try to guess what wiki is this log entry associated with
     * @return string|null
     */
    public function tryFigureAssociatedWiki()
    {
        // This is super hacky code, but until https://github.com/UTRS2/utrs/issues/139 is fixed this is the best I can do
        $class = null;
        if (Str::startsWith($this->objecttype, 'App')) {
            $class = $this->objecttype;
        } else if ($this->objecttype === 'appeal') {
            $class = Appeal::class;
        }

        if (!$class) {
            return null;
        }

        $object = $class::where('id', $this->referenceobject)->first();

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
