<?php

namespace App;

use App\Concert;
use Carbon\Carbon;
use App\Facades\TicketCode;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    
    protected $guarded =[];
    //
    public function scopeAvailable($query)
    {
    	return $query->whereNull('order_id')->whereNull('reserved_at');
    }

    public function reserve()
    {
        $this->update(['reserved_at' => Carbon::now()]);
    }

    public function release()
    {
    	$this->update(['order_id'=>null,'reserved_at' => NULL]);
    }

    public function claimFor(Order $order)
    {
        $this->code = TicketCode::generateFor($this);
        $order->tickets()->save($this);
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function getPriceAttribute()
    {
        return $this->concert->ticket_price; 
    }

    
}
