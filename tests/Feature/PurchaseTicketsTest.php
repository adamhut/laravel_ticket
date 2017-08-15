<?php

namespace Tests\Feature;

use App\Concert;
use Tests\TestCase;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected $paymentGateway;
    protected $response;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class,$this->paymentGateway);

    }

    private function orderTickets($concert,$params)
    {
        $this->response = $this->json('POST',"/concerts/{$concert->id}/orders",$params);   
    }

    private function assertValidationError($field)
    {
        $this->response->assertStatus(422);
        $this->assertArrayHasKey($field,$this->response->decodeResponseJson());
    }


    /** @test */
    public function customer_can_purchase_tickets_to_a_published_concert()
    {
        //$this->withExceptionHandling();
        //Arrange
        //create a concert
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price'=>3250,
        ])->addTickets(3);
        
        //Act
        //Purchase concert ticekts
        $this->orderTickets($concert,[
            'email'=> 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
       
        //Assert
        $this->response->assertStatus(201);

        //Make sure the customer was Charged the correct amount
        $this->assertEquals(9750,$this->paymentGateway->totalCharges());

        //Make sure that an order existes for this customer

        $this->assertTrue($concert->hasOrderFor('john@example.com'));

        $this->assertEquals(3,$concert->ordersFor('john@example.com')->first()->ticketQuantity());
        
    }

    /** @test */
    public function can_not_puchase_to_an_unpublished_concert()
    {
        $this->withExceptionHandling();

        $concert = factory(Concert::class)->states('unpublished')->create()->addTickets(3);
       
        //Purchase concert ticekts
        $this->orderTickets($concert,[
            'email'=> 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->response->assertStatus(404);

        $this->assertFalse($concert->hasOrderFor('john@example.com'));

        $this->assertEquals(0,$this->paymentGateway->totalCharges());

    }

    /** @test */
    public function an_order_is_not_created_if_payment_fails()
    {
        $this->withExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price'=>3250,
        ])->addTickets(3);
        

        //Purchase concert ticekts
        $this->orderTickets($concert,[
            'email'=> 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token',
        ]);

        $this->response->assertStatus(422);
        //$order=$concert->orders()->where('email','john@example.com')->first();

        $this->assertFalse($concert->hasOrderFor('john@example.com'));

    }

    /** @test */
    public function email_is_required_to_purchase_tickets()
    {
        $this->withExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert,[
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
       
    }

    /** @test */
    public function email_must_be_valid_to_purhase_tickets()
    {
        $this->withExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert,[
            'email' => 'not-an-email-address',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchased_tickets()
    {
        $this->withExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert,[
            'email' => 'john@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
     
    }

    /** @test */
    public function ticket_quantity_must_be_at_leaset_1_to_purchased_tickets()
    {
        $this->withExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert,[
            'email' => 'john@example.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

         $this->assertValidationError('ticket_quantity');
      
    }


    /** @test */
    public function payment_token_is_required()
    {
        $this->withExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert,[
            'email' => 'john@example.com',
            'ticket_quantity' => 1,
        ]);

        $this->assertValidationError('payment_token');
    }

    /** @test */
    public function can_not_purchase_more_tickets_than_remain()
    {
        $this->withExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create([])->addTickets(50);  

        $this->orderTickets($concert,[
            'email' => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->response->assertStatus(422);
        
        
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0,$this->paymentGateway->totalCharges());

        $this->assertEquals(50,$concert->ticketsRemaining());
    }

}
