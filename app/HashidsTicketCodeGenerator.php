<?php
namespace App;

// /use Hashids\Hashids;


class HashidsTicketCodeGenerator implements TicketCodeGenerator
{
	protected $hashid;

	public function __construct($salt='')
	{
		$this->hashid = new \Hashids\Hashids($salt,6,'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
	}

	public function generateFor($ticket)
	{
			
		return $this->hashid->encode($ticket->id);	
	}
}