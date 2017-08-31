<?php

namespace Tests\Feature;

use Mockery;
use App\Concert;
use Tests\TestCase;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use App\Facades\OrderConfirmationNumber;
use App\OrderConfirmationNumberGenerator;
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
        $savedRequest = $this->app['request'];
        $this->response = $this->json('POST',"/concerts/{$concert->id}/orders",$params);   
        $this->app['request']=$savedRequest;
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
        
        //$orderConfirmationGenerator->generate();
        
        /*
        $orderConfirmationGenerator = Mockery::mock(OrderConfirmationNumberGenerator::class,[
            'generate' => 'ORDERCONFIRMATION1234'
        ]);

        $this->app->instance(OrderConfirmationNumberGenerator::class, $orderConfirmationGenerator);
        */
        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATION1234');

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
        $this->response
            ->assertStatus(201)
            ->assertJson([
                'confirmation_number' => 'ORDERCONFIRMATION1234',
                'email' => 'john@example.com',
                //'ticket_quantity' => 3,
                'amount' => 9750,
                'ticket' => [
                    ['code'=>'ticketCode1'],
                    ['code'=>'ticketCode2'],
                    ['code'=>'ticketCode3'],
                    
                ]

            ]);
       

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
        $this->assertEquals(3, $concert->ticketsRemaining());

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

    /** @test */
    public function cannot_purchase_tickets_andother_customer_is_already_trying_to_purchase()
    {

        $concert = factory(Concert::class)->states('published')->create([
                'ticket_price' => 1200
            ])->addTickets(3);  

        $this->paymentGateway->beforeFirstCharge(function($paymentGateway) use($concert){
           
            $this->orderTickets($concert,[
                'email' => 'personB@example.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]);

            $this->response->assertStatus(422); 
            $this->assertFalse($concert->hasOrderFor('personB@example.com'));
            $this->assertEquals(0,$this->paymentGateway->totalCharges());

        });

        //Find ticekts for person A
        $this->orderTickets($concert,[
            'email' => 'personA@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
                                        //Find ticekts for person b
                                        //Attempt to charge person B
                                        //Create an order for person B
        
        //Attempt to charge person A
        //Create an order for person A
        $this->assertEquals(3600,$this->paymentGateway->totalCharges());

        //Make sure that an order existes for this customer
        //dd($concert->orders()->first()->toArray());
        $this->assertTrue($concert->hasOrderFor('personA@example.com'));

        $this->assertEquals(3,$concert->ordersFor('personA@example.com')->first()->ticketQuantity());

        

    }

}
