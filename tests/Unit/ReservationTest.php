<?php

namespace Tests\Unit;

use App\Concert;
use Tests\TestCase;
use App\Reservation;
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

        $reservation = new Reservation($tickets);

        $this->assertEquals(6000, $reservation->totalCost());
    }
}
