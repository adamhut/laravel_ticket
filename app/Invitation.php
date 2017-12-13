<?php

namespace App;

use App\User;
use App\Mail\InvitationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    //
    protected $fillable=[
        'user_id',
        'email',
        'code'
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

    public function send()
    {
        Mail::to($this->email)->send(new InvitationEmail($this));
    }
}
