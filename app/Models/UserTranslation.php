<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'appeal_id',
    ];

    // no timestamps
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appeal()
    {
        return $this->belongsTo(Appeal::class);
    }

}
