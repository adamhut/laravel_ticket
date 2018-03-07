<?php
namespace App\Billing;

use Stripe\Token;

use Stripe\Error\InvalidRequest;


class StripePaymentGateway implements PaymentGateway
{
    const TEST_CARD_NUMBER= '4242424242424242';

    protected $charges;
    protected $apiKey;
    protected $beforeFirstChargeCallback=null;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount,$token,$destinationAccountId)
    {
        try{
            $stripeCharge = \Stripe\Charge::create([
                "amount" => $amount,
                "currency" => "usd",
                "source" => $token, // obtained with Stripe.js
                "destination" =>[
                    'account'   => $destinationAccountId,
                    'amount'    => $amount * 0.9,
                ]
                //"description" => "Charge for matthew.smith@example.com"
            ],$this->apiKey);

            return new Charge([
                'amount' => $stripeCharge['amount'],
                'card_last_four' => $stripeCharge['source']['last4'],
                'destination' => $destinationAccountId
            ]);

        }catch(InvalidRequest $e)
        {
            throw new PaymentFailedException;
        }
    }

    public function getValidTestToken($cardNumber=self::TEST_CARD_NUMBER)
    {
        return Token::create([
            "card" => [
                "number" => $cardNumber,
                "exp_month" => 1,
                "exp_year" => date('Y')+1,
                "cvc" => "123"
            ]
        ],['api_key'=>$this->apiKey])->id;
    }

    public function newChargesDuring($callback)
    {
        $lastestCharge = $this->lastCharge();
        $callback($this);
        return $this->newChargesSince($lastestCharge)->map(function($stripeCharge){
            return new Charge([
                'amount' => $stripeCharge['amount'],
                'card_last_four' => $stripeCharge['source']['last4'],
            ]);
        });
        // /return $newCharge;
    }

    private function lastCharge()
    {
        return array_first(\Stripe\Charge::all(
            ['limit'=>1],
            ['api_key'=>$this->apiKey]
        )['data']);
    }

    private function newChargesSince($charge=null)
    {
        $newCharges =\Stripe\Charge::all(
            [
                //'limit'=>1,
                'ending_before'=>$charge? $charge->id:null,
            ],
            ['api_key'=>$this->apiKey]
        )['data'];
        return collect($newCharges);
    }
    /*
    public function chargeWithGuzzle($amount,$token)
    {
        (new \GuzzleHttp\Client)->post('https:\\api.stripe.com/v1/charges',[
            'header' => [
                'Authorization' => "Bearer {$this->apiKey}",
            ],
            'form_params' => [
                'amount' =>  $amount,
                'source' => $token,
                'currency' => 'usd'
            ],
        ]);
    }
    */
}
