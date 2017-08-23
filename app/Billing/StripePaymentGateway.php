<?php
namespace App\Billing;

use Stripe\Token;
use Stripe\Charge;
use Stripe\Error\InvalidRequest;


class StripePaymentGateway implements PaymentGateway
{

    protected $charges;
    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }
    protected $beforeFirstChargeCallback=null;

    public function charge($amount,$token)
    {
        try{
            $result = Charge::create([
                "amount" => $amount,
                "currency" => "usd",
                "source" => $token, // obtained with Stripe.js
                //"description" => "Charge for matthew.smith@example.com"
            ],$this->apiKey);
        }catch(InvalidRequest $e)
        {
            throw new PaymentFailedException;
        }
    }

    public function getValidTestToken()
    {
        return Token::create([
            "card" => [
                "number" => "4242424242424242",
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
        return $this->newChargesSince($lastestCharge)->pluck('amount');
        // /return $newCharge;
    }

    private function lastCharge()
    {
        return Charge::all(
            ['limit'=>1],
            ['api_key'=>$this->apiKey]
        )['data'][0];
    }

    private function newChargesSince($charge=null)
    {
        $newCharges =Charge::all(
            [
                'limit'=>1,
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
