<?php

namespace App;

use RuntimeException;
use Illuminate\Database\Eloquent\Model;

class Appeal extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
	protected $attributes = [
        'privacylevel' => 0,
        'blockfound' => 0
    ];
    public function comments()
    {
        return $this->hasMany('App\Log', 'referenceobject','id');
    }

    public function getWikiEmailUsername()
    {
        // based on source code 0 seems to mean ip, 1 = direct user block, and 2 = ip below account
        // we can't send e-mail to IPs
        if ($this->blocktype === 0) {
            throw new RuntimeException('Can not send an e-mail to an IP address');
        }

        return $this->appealfor;
    }
}
