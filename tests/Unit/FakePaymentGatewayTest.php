<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FakePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(2500,$paymentGateway->getValidTestToken());

        $this->assertEquals(2500,$paymentGateway->totalCharges());
    }

    /** @test */
    public function charges_with_invalid_payment_token_fail()
    {
    	try {
	        $paymentGateway = new FakePaymentGateway;
	        $paymentGateway->charge(2500,'invalid-payment-token');
    		
    	} catch (PaymentFailedException $e) {
    		//$this->assertEquals(2500, actual);
    		return ;
    	}

    	$this->fail();
    }

    /** @test */
    public function running_a_hook_before_the_first_charge()
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

    /** @test */
    public function running_a_hook_only_once()
    {
        $paymentGateway = new FakePaymentGateway;
        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function($paymentGateway) use(&$callbackRan){
            $paymentGateway->charge(2500,$paymentGateway->getValidTestToken());
            $callbackRan++;
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500,$paymentGateway->getValidTestToken());
        $this->assertEquals(1,$timesCallbackRan);

        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }
}
