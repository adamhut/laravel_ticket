<?php

namespace Tests\Unit\Billing;

use Tests\TestCase;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
//use Tests\Unit\PaymentGatewayContractTests;


class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function getPaymentGateway()
    {
        return new FakePaymentGateway;
    }

    /** @test */
    public function running_a_hook_before_the_first_charge()
    {
         $paymentGateway = new FakePaymentGateway;
        $timesCallbackRan = 0;
        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
      
    }

    
    public function running_a_hook_only_once()
    {
        $paymentGateway = new FakePaymentGateway;
        $callbackRan = false;

        $paymentGateway->beforeFirstCharge(function($paymentGateway) use(&$callbackRan){
            $callbackRan=true;
            $this->assertEquals(0, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500,$paymentGateway->getValidTestToken());
        $this->assertTrue($callbackRan);

        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }
}
