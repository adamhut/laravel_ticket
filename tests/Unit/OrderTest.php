<?php

namespace Tests\Unit;

use App\Order;
use App\Ticket;
use App\Concert;
use Tests\TestCase;
use App\Reservation;
use App\Billing\Charge;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function creating_an_order_from_tickets_email_and_charge()
    {

        //$concert = factory(Concert::class)->create()->addTickets(5);
        //$this->assertEquals(5,$concert->ticketsRemaining());
        $tickets = factory(Ticket::class,3)->create();
        $charge = new Charge([
            'amount'=>3600,
            'card_last_four' => '1234',
        ]);
       
        $order = Order::forTickets($tickets,'jane@example.com',$charge);

        $this->assertEquals('jane@example.com',$order->email);
        $this->assertEquals(3,$order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four); 
        //$this->assertEquals(2,$concert->ticketsRemaining());
    }

    /** @test */
    public function retriving_an_order_by_confirmation_number()
    {
        $order = factory(Order::class)->create([
                'confirmation_number' => 'ORDERCONFIRMATION1234',
            ]);
        
        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');

        $this->assertEquals($order->id, $foundOrder->id);
    }

    /** @test */
    public function retrieving_an_nonexistent_order_by_confirmation_number_throws_an_exception()
    {
        try{
            Order::findByConfirmationNumber('NONEEXISTENTCONFIRMATIONBNUMBER');
        }catch(ModelNotFoundException $e)
        {
            return ;
        }
        $this->fail('No Matching Oder was found fot the specified confirmation number, but an exception was not throw');
    }

    /** @test */
    public function coverting_to_an_array()
    {
        // $concert = factory(Concert::class)->create(['ticket_price'=>1200])->addTickets(5);
        //$order = $concert->orderTickets('jane@example.com',5);

        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'=>'jane@example.com',
            'amount' => 6000,
        ]);
        $order->tickets()->saveMany(factory(Ticket::class,5)->create());

        $result = $order->toArray();

        $this->assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'=>'jane@example.com',
            'ticket_quantity'=> 5,
            'amount' => 6000,
        ], $result);
    }


}
