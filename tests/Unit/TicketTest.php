<?php

namespace Tests\Unit;

use App\Ticket;
use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TicketTest extends TestCase
{
    use  DatabaseMigrations;

    /** @test */
    public function a_ticket_can_be_reserve()
    {
        $ticket = factory(Ticket::class)->create();   

        $this->assertNull($ticket->reserve_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserve_at);

    }

    /** @test */
    public function a_ticket_can_be_released()
    {
        //Arrange
        $ticket = factory(Ticket::class)->states('reserved')->create();
        
        $this->assertNull($ticket->reserve_at);

        //Act
        $ticket->release();

        //Assert
        $this->assertNull($ticket->fresh()->reserve_at);
    }

}
