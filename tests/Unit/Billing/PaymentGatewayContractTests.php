<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;

trait PaymentGatewayContractTests
{

	abstract protected function getPaymentGateway();
    
	/** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        //Create a stripePaymentGateway
        $paymentGateway = $this->getPaymentGateway();
        //$charge = $paymentGateway->lastCharge();
        //Create a new carge for some amount using a valid token
        // /$paymentGateway->charge(2500,$paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway){
            $paymentGateway->charge(2500,$paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(2500,$newCharges->map->amount()->sum());

        //Verify that the charge was completed successfull
        //$this->assertCount(1, $this->newCharges($this->lastCharge));
        //$this->assertCount(1, $this->newCharges());
        //$this->assertEquals(2500,$this->lastCharge()->amount);
    }

    /** @test */
    public function can_get_details_about_a_successful_charge()
    {
        $paymentGateway = $this->getPaymentGateway();
        $charge = $paymentGateway->charge(2500,$paymentGateway->getValidTestToken($paymentGateway::TEST_CARD_NUMBER), env('STRIPE_TEST_PROMOTER_ID') );

        $this->assertEquals(substr($paymentGateway::TEST_CARD_NUMBER,-4), $charge->cardLastFour());
        $this->assertEquals(2500, $charge->amount());
        $this->assertEquals('test_acct_1234', $charge->destination());
    }



    /** @test */
    public function charges_with_invalid_payment_token_fail()
    {
    
	    $paymentGateway = $this->getPaymentGateway();
	    //$paymentGateway->charge(2500,'invalid-payment-token');
        
        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway){
            try {
                $paymentGateway->charge(2500,'invalid-payment-token', env('STRIPE_TEST_PROMOTER_ID') );
            
        	} catch (PaymentFailedException $e) {
        		//$this->assertEquals(2500, actual);
        		return ;
        	}
            $this->fail('charging with invalid payment token did not throw a PaymentFailedException');
        });
        
        $this->assertCount(0, $newCharges);
    	
    }

    /** @test */
    public function can_fetch_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();
        $paymentGateway->charge(2000,$paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));
        $paymentGateway->charge(3000,$paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));

        $newCharges = $paymentGateway->newChargesDuring(function($paymentGateway){
            $paymentGateway->charge(4000,$paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));
            $paymentGateway->charge(5000,$paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));
        });
        //dd($newCharges);
        $this->assertCount(2, $newCharges);
        $this->assertEquals([5000,4000],$newCharges->map->amount()->all());
    }


}