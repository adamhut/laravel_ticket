<?php

namespace App;

use App\Concert;
use Illuminate\Database\Eloquent\Model;
use App\Facades\OrderConfirmationNumber;
//use App\OrderConfirmationNumberGenerator;

class Order extends Model
{
    //
    protected $guarded=[];

    public static function forTickets($tickets,$email,$charge)
    {
        $order = self::create([
            //'confirmation_number' => app(OrderConfirmationNumberGenerator::class)->generate(),
            'confirmation_number' => OrderConfirmationNumber::generate(),
            //'concert_id' =>$this->id,
            'email'=>$email,
            'amount' => $charge->amount(),
            'card_last_four' => $charge->cardLastFour(),
        ]);

        foreach($tickets as $ticket){
            $order->tickets()->save($ticket);
        }

        return $order;
    }



    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /*
    public function cancel()
    {
		foreach($this->tickets as $ticket)
		{
			$ticket->release();
		}
		$this->delete();
    }
    */

    public function ticketQuantity()
    {
    	return $this->tickets()->count();
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function toArray()
    {
        return [
            'confirmation_number'=>$this->confirmation_number,
            'email'=>$this->email,
            'ticket_quantity'=> $this->ticketQuantity(),
            'amount' => $this->amount,
        ];
    }


    public static function findByConfirmationNumber($confirmationNumebr)
    {
        return self::where('confirmation_number',$confirmationNumebr)->firstOrFail();
    }
}
