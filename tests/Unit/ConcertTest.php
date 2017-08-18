<?php

namespace Tests\Unit;

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function can_get_formatted_date()
    {
        //Create a concert with a known date
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        //retrieve the formatted date
        //verify the date
        $this->assertEquals('December 1, 2016',$concert->formatted_date);
    }

    /** @test */
    public function can_get_formatted_start_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        $this->assertEquals('5:00pm',$concert->formatted_start_time);
    }

    /** @test */
    public function can_get_ticekt_price_in_dollars()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750,
        ]);

        $this->assertEquals('67.50',$concert->ticket_price_in_dollars);
    }

    /** @test */
    public function concert_with_a_published_at_date_are_published()
    {
        $publisedConcertA = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week'),
        ]);
        $publisedConcertB = factory(Concert::class)->create([
            'published_at' => Carbon::parse('-1 week'),
        ]);
        $unpublisedConcertC = factory(Concert::class)->create([
            'published_at' => null,
        ]);

        $publisedConcerts = Concert::published()->get();

        $this->assertTrue($publisedConcerts->contains($publisedConcertA));
        $this->assertTrue($publisedConcerts->contains($publisedConcertB));
        $this->assertFalse($publisedConcerts->contains($unpublisedConcertC));
    }


    /** @test */
    public function can_order_concert_tickets()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        

        $order = $concert->orderTickets('jane@example.com',3);

        $this->assertEquals('jane@example.com', $order->email);

        $this->assertEquals(3, $order->ticketQuantity());
    }

    /** @test */
    public function can_add_ticket()
    {
        $concert = factory(Concert::class)->create();

        $concert->addTickets(50);

        $this->assertEquals(50,$concert->ticketsRemaining());

    }

    /** @test */
    public function ticket_remaining_does_not_include_ticket_associated_with_an_order()
    {
        $concert = factory(Concert::class)->create()->addTickets(50);

        $order = $concert->orderTickets('jane@example.com',30);

        $this->assertEquals(20, $concert->ticketsRemaining());

    }

    /** @test */
    public function trying_to_order_more_ticket_than_remain_throw_exception()
    {
        $this->withExceptionHandling();

        $concert = factory(Concert::class)->create()->addTickets(10);

        try{
            $order = $concert->orderTickets('jane@example.com',11);
        }catch(NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('jane@example.com'));
            $this->assertEquals(10,$concert->ticketsRemaining()); 
            return ;
        }

        $this->fail('order successed even though there were not enough tickets remianing.');
    }

    /** @test */
    public function can_not_order_ticket_that_already_been_purchased()
    {
        $this->withExceptionHandling();
        $concert = factory(Concert::class)->create()->addTickets(10);
        
        $concert->orderTickets('jane@example.com',8);
      
        try{
            $concert->orderTickets('john@example.com',3);
        }catch(NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('john@example.com'));
            $this->assertEquals(2,$concert->ticketsRemaining()); 
            return ;
        }

        $this->fail('order successed even though there were not enough tickets remianing.');
    }

    /** @test */
    public function can_reserve_available_tickets()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $this->assertEquals(3,$concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2,'john@example.com');

        $this->assertCount(2, $reservation->tickets());
        $this->assertEquals(1,$concert->ticketsRemaining());
        $this->assertEquals('john@example.com',$reservation->email());
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_puchased()
    {
         $this->withExceptionHandling();
        $concert = factory(Concert::class)->create()->addTickets(3);
        $concert->ordertickets('jane@example.com',2);

        try{
            $concert->reserveTickets(2,'jane@example.com');
        }catch(NotEnoughTicketsException $e) {
            //$this->assertFalse($concert->hasOrderFor('john@example.com'));
            $this->assertEquals(1,$concert->ticketsRemaining()); 
            return ;
        }
        $this->fail('Reserveing ticket success even the ticket were already sold.');
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_resevered()
    {
        $this->withExceptionHandling();
        $concert = factory(Concert::class)->create()->addTickets(3);
        $concert->reserveTickets(2,'jane@example.com');

        try{
            $concert->reserveTickets(2,'john@example.com');
        }catch(NotEnoughTicketsException $e) {
            //$this->assertFalse($concert->hasOrderFor('john@example.com'));
            $this->assertEquals(1,$concert->ticketsRemaining()); 
            return ;
        }
        $this->fail('Reserveing ticket success even the ticket were already reserved.');
    }

}
