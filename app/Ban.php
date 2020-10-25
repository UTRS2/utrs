<?php

namespace App;

use App\Utils\IPUtils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ban extends Model
{
    use HasFactory;
    
	protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $appends = ['formatted_expiry'];

    protected $casts = [
        'is_protected' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function logs()
    {
        return $this->morphMany(Log::class, 'object', 'objecttype', 'referenceobject');
    }

    public function scopeActive(Builder $query)
    {
        return $query
            ->where('is_active', 1)
            ->where(function (Builder $query) {
                // this treats bans whose expire before start of 2000 as indefinite because no real ban will expire before that
                // and that's simpler than comparing it to a specific point because timezones
                return $query
                    ->where('expiry', '>=', now())
                    ->orWhere('expiry', '<=', '2000-01-01 00:00:00');
            });
    }

    public function setTargetAttribute($value)
    {
        if (IPUtils::isIpRange($value)) {
            $value = IPUtils::normalizeRange($value);
        }

        $this->attributes['target'] = $value;
    }

    public function getFormattedExpiryAttribute()
    {
        $expiry = Carbon::createFromFormat('Y-m-d H:i:s', $this->expiry);
        return $expiry->year >= 2000 ? $this->expiry : 'indefinite';
    }

    /**
     * @param string ...$targets
     * @return array
     */
    public static function getTargetsToCheck(...$targets)
    {
        return collect(func_get_args())
            ->flatten()
            ->filter(function ($it) {
                // truthy values only, removes nulls, empty strings, etc
                return !!$it;
            })
            ->flatMap(function (string $it) {
                if (IPUtils::isIpRange($it)) {
                    $it = IPUtils::cutCidrRangePart($it);
                }

                if (IPUtils::isIp($it)) {
                    return array_merge([$it], IPUtils::getAllParentRanges($it));
                }

                return [$it];
            })
            ->toArray();
    }
}
