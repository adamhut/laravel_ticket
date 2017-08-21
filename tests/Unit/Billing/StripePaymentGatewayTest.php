<?php

namespace Tests\Unit\Billing;

use Stripe\Token;
use Stripe\Charge;
use Stripe\Stripe;
use Tests\TestCase;
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StripePaymentGatewayTest extends TestCase
{
    protected $lastCharge;

    protected function setUp()
    {
        parent::setUp();
        $this->lastCharge = $this->lastCharge();
    }

    private function lastCharge()
    {
        return Charge::all(
            ['limit'=>1],
            ['api_key'=>config('services.stripe.secret')]
        )['data'][0];
    }

    private function newCharges()
    {
        return Charge::all(
            [
                'limit'=>1,
                'ending_before'=>$this->lastCharge,
            ],
            ['api_key'=>config('services.stripe.secret')]
        )['data'];
    }

    private function validToken()
    {
        return Token::create([
            "card" => [
                "number" => "4242424242424242",
                "exp_month" => 1,
                "exp_year" => date('Y')+1,
                "cvc" => "123"
            ]
        ],['api_key'=>config('services.stripe.secret')])->id;
    }

    /** @test */
    public function charges_with_a_valid_payment_token_are_successfull()
    {
        //Create a stripePaymentGateway
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        //Create a new carge for some amount using a valid token
        $paymentGateway->charge(2500,$this->validToken());

        //Verify that the charge was completed successfull
        $this->assertCount(1, $this->newCharges($this->lastCharge));
        $this->assertEquals(2500,$this->lastCharge()->amount);
    }
}
