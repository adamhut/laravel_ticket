<?php

namespace Tests\Unit;

use App\Order;
use App\Ticket;
use App\Concert;
use Tests\TestCase;
use App\Facades\TicketCode;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TicketTest extends TestCase
{
    use  DatabaseMigrations;

    /** @test */
    public function a_ticket_can_be_reserve()
    {
        $ticket = factory(Ticket::class)->create();   

        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);

    }

    /** @test */
    public function a_ticket_can_be_released()
    {
        //Arrange
        $ticket = factory(Ticket::class)->states('reserved')->create();
        
        $this->assertNotNull($ticket->reserved_at);

        //Act
        $ticket->release();

        //Assert
        $this->assertNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function a_tickets_can_be_claim_for_an_order()
    {
        $order = factory(Order::class)->create();
        $ticket =factory(Ticket::class)->create(['code'=>null]);

        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');
        
        $this->assertNull($ticket->fresh()->code);
        $ticket->claimFor($order);

        //assert that the ticket is saved to the order
        //$this->assertEquals($order->id,$ticket->order_id);
        $this->assertContains($ticket->id,$order->tickets->pluck('id'));
        $this->assertEquals('TICKETCODE1', $ticket->fresh()->code);
        //assert that the ticket had the expected ticket code generated
        
        
    }

}
