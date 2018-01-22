<?php

namespace App;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_changed_at',
        'stripe_account_id',
        'stripe_access_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function concerts()
    {
        return $this->hasMany(Concert::class);
    }

    public function isPasswordExpired()
    {
        $passwordChangeAt = new Carbon(($this->password_changed_at) ?  $this->password_changed_at : $this->created_at);

        return !! (Carbon::now()->diffInDays($passwordChangeAt) > config('auth.password_expires_days'));

    }
}
