<?php

namespace Tests\Unit;

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
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
        $concert = factory(Concert::class)->create();

        $order = $concert->orderTickets('jane@example.com',3);

        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());


    }
}
