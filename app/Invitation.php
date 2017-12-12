<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    //
    protected $fillable=[
        'user_id'
    ];

    public static function findByCode($code)
    {
        return self::where('code',$code)->firstOrFail();   
    }

    public function hasBeenUsed()
    {
        return $this->user_id !== null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
