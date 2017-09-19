<?php

namespace App;

use App\Concert;
use Illuminate\Database\Eloquent\Model;

class AttendeeMessage extends Model
{
    //
    protected $guarded =[];


    public function recipients()
    {
        return $this->concert->orders()->pluck('email');
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function orders()
    {
        return $this->concert->orders();
    }

    public function withChunkedRecipients($chunkSize=20,$callback)
    {
        $this->orders()->chunk($chunkSize,function($orders) use ($callback){
            //dd($recipient);
            $callback($orders->pluck('email'));
        });
    }
}
