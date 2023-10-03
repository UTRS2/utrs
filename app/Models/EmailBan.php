<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailBan extends Model
{
    use HasFactory;

    // set the table name
    protected $table = 'emails';

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $with = ['linkedappeals'];

    // set the 'linkedappeals' attribute to return the linked appeals with a many-to-one relationship  
    public function linkedappeals()
    {
        return $this->hasMany(Appeal::class, 'id', 'linkedappeals');
    }

    // convert any attribute to a human readable format from a datetime
    public function humanFormat($value)
    {
        return date('d/m/Y H:i:s', strtotime($value));
    }



    
}
