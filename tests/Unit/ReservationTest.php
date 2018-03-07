<?php

namespace Tests\Unit;
use Mockery;
use App\Ticket;
use App\Concert;
use Tests\TestCase;
use App\Reservation;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function calculation_the_total_cost()
    {
        $tickets = collect([
        	(object)['price'=>1200],
        	(object)['price'=>1200],
        	(object)['price'=>1200],
        	(object)['price'=>1200],
        	(object)['price'=>1200],
        ]);

        $reservation = new Reservation($tickets,'john@example.com');

        $this->assertEquals(6000, $reservation->totalCost());
    }

    /** @test */
    public function retrieving_the_reservation_tickets()
    {
        $tickets = collect([
            (object)['price'=>1200],
            (object)['price'=>1200],
            (object)['price'=>1200],
            (object)['price'=>1200],
            (object)['price'=>1200],
        ]);

        $reservation = new Reservation($tickets,'john@example.com');

        $this->assertEquals($tickets, $reservation->tickets());
    }

     /** @test */
    public function retrieving_the_reservation_email()
    {
        $reservation = new Reservation(collect(),'john@example.com');

        $this->assertEquals('john@example.com', $reservation->email());
    }

    /** @test */
    public function resever_tickets_are_released_when_reservation_is_cancel()
    {
        /*
            Mockery::mock(Ticket::class,function($mock){
                $mock->shouldReceive('release')->once();
            })

        $tickets = collect([
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock()

        ]);
        */

        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class)

        ]);
        $reservation = new reservation($tickets,'john@example.com');

        $reservation->cancel();

        foreach ($tickets as $ticket) {
           $ticket->shouldHaveReceived('release');
        }
    }

    /** @test */
    public function completing_a_reservation()
    {
        $email = 'john@example.com';
        $concert = factory(Concert::class)->create(['ticket_price'=>1200]);
        $tickets = factory(Ticket::class,3)->create(['concert_id'=>$concert->id]);

        $reservation = new Reservation($tickets,$email);

        $paymentGateway = new FakePaymentGateway;

        $order = $reservation->complete($paymentGateway,$paymentGateway->getValidTestToken(),'test_acct_1234');

        $this->assertEquals($email, $order->email);
        $this->assertEquals(3,$order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);

        $this->assertEquals(3600, $paymentGateway->totalChargesFor('test_acct_1234'));

    }


}
