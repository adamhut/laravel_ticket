<?php

namespace App;

use App\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotEnoughTicketsException;

class Concert extends Model
{
    //
    protected $guarded = [];

    protected $dates = ['date'];


    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function isPublished()
    {
        return $this->published_at !==null;
    }

    public function publish()
    {
        $this->update(['published_at'=>Carbon::now()]);//
        //or
        //$this->update['published_at'=>$this->freshTimestamp()];//
        
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price/100,2);
    }

    public function orders()
    {
        //return $this->hasMany(Order::class);
        return $this->belongsToMany(Order::class,'tickets');
    }

    public function hasOrderFor($customerEmail)
    {
        return $this->orders()->where('email',$customerEmail)->count() >0;
    }

    public function ordersFor($customerEmail)
    {
        return $this->orders()->where('email',$customerEmail)->get();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    public function orderTickets($email,$ticketQuantity)
    {
        $tickets = $this->findTickets($ticketQuantity);

        return $this->createOrder($email,$tickets);
      
    }
    */

    public function reserveTickets($quantity,$email)
    {
        $tickets= $this->findTickets($quantity)->each(function($ticket){
            $ticket->reserve();
        });

        return new Reservation($tickets,$email);
    }

    public function findTickets($quantity)
    {
        //Find the tickets
        $tickets = $this->tickets()->available()->take($quantity)->get();
       
        if($tickets->count() < $quantity )
        {
            throw new NotEnoughTicketsException;
        } 

        return $tickets; 
    }

    
    /*
    public function createOrder($email ,$tickets)
    {
        return Order::forTickets($tickets,$email,$tickets->sum('price'));
        $order = Order::create([
            //'concert_id' =>$this->id,
            'email'=>$email,
            'amount' => $tickets->sum('price'),
        ]);

        foreach($tickets as $ticket){
            $order->tickets()->save($ticket);
        }

        return $order;   
    }
    */

    public function addTickets($quantity)
    {
        foreach(range(1,$quantity) as $i){
            $this->tickets()->create([]);
        }
        return $this;
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

}
