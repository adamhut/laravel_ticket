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

  
}
