<?php

namespace Tests\Feature;

use App\Order;
use App\Ticket;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewOrderTest extends TestCase
{
	use DatabaseMigrations;
	/** @test */
	public function user_can_view_their_order_confirmation()
	{
		//arrange
		//create a concert
		$concert = factory(Concert::class)->states('published')->create([
            'ticket_price'=>3250,
        ]);
		//create an order
        $order = factory(Order::class)->create();
        //create a ticket 
        $ticket = factory(Ticket::class)->create([
        	'concert_id' =>$concert->id,
        	'order_id' => $order->id,
        ]);

		//act
		//visit the order confirmation page
		$response = $this->get("orders/{$order->confirmation_number}");

		//assert
		//Assert we see the correct order details
		
	}
}
