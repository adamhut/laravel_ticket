<?php 
namespace App;

class Reservation 
{
	protected $tickets;
	protected $email;

	public function __construct($tickets, $email)
	{
		$this->tickets = $tickets;
		$this->email = $email;
	}

	
	public function totalCost()
	{	
		return $this->tickets->sum('price')	;
	}

	public function cancel()
	{
		$this->tickets->each(function($ticket){
			$ticket->release();
		});
		return ;  
	}

	public function tickets()
	{
		return $this->tickets;
	}

	public function email()
	{
		return $this->email;
	}
}