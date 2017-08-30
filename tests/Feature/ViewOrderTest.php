<?php

namespace Tests\Feature;

use App\Order;
use App\Ticket;
use App\Concert;
use Carbon\Carbon;
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
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date' => Carbon::parse('March 12, 2017 8:00pm'),
            'ticket_price' => 4250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
        ]);
		//create an order
		$orderConfirmation='OrderConfirmation_1234';
        $order = factory(Order::class)->create([
        	'confirmation_number' => $orderConfirmation,
        	'card_last_four' => '1881',
        	'amount' => 8500,
        	 'email' => 'john@example.com',
        ]);
        //create a ticket 
        $ticketA = factory(Ticket::class)->create([
        	'concert_id' =>$concert->id,
        	'order_id' => $order->id,
        	'code' => 'TICKETCODE123',
        ]);

        $ticketB = factory(Ticket::class)->create([
        	'concert_id' =>$concert->id,
        	'order_id' => $order->id,
        	'code' => 'TICKETCODE456',
        ]);

		//act
		//visit the order confirmation page
		$response = $this->get("orders/".$orderConfirmation);

		//assert
		//Assert we see the correct order details
		$response->assertStatus(200)
			->assertViewHas('order', function($viewOrder) use ($order){
				return $order->id === $viewOrder->id;
			})
			->assertSee($orderConfirmation)
			->assertSee('$85.00')
			->assertSee('**** **** **** 1881')
			->assertSee('TICKETCODE123')
			->assertSee('TICKETCODE456')
			->assertSee('The Red Chord')
	       	->assertSee('with Animosity and Lethargy')
	       	->assertSee('The Mosh Pit')
	       	->assertSee('123 Example Lane')
	       	->assertSee('Laraville, ON')
	       	->assertSee('17916')
	       	->assertSee('john@example.com')
	       	->assertSee('2017-03-12 20:00');
	       	//->assertSee('March 12, 2017')
	       	//->assertSee('8:00pm');
	}
}
