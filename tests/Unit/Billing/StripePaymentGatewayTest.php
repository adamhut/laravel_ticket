<?php

namespace Tests\Unit\Billing;


use Stripe\Charge;
use Tests\TestCase;
use App\Billing\StripePaymentGateway;
use App\Billing\PaymentFailedException;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function setUp()
    {
        parent::setUp();
        
    }   

    protected function getPaymentGateway()
    {
        return  new StripePaymentGateway(config('services.stripe.secret'));
    }

    /** @test */
    public function ninety_percent_of_the_payment_is_transfered_to_the_destination_account()
    {
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        $paymentGateway->charge(5000,$paymentGateway->getValidTestToken(),env('STRIPE_TEST_PROMOTER_ID'));

        $lastStripeCharge = array_first(\Stripe\Charge::all(
            ['limit' => 1],
            ['api_key' => config('services.stripe.secret')]
        )['data']);

        //dd($lastStripeCharge)
        
        $this->assertEquals(5000,$lastStripeCharge['amount']);

        $this->assertEquals(env('STRIPE_TEST_PROMOTER_ID'), $lastStripeCharge['destination']);

        $transfer= \Stripe\Transfer::retrieve($lastStripeCharge['transfer'], ['api_key' => config('services.stripe.secret')]);

        //dd($transfer);

        $this->assertEquals(4500, $transfer['amount']);



    }

  
}
