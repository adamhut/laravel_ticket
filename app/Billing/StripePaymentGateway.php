<?php
namespace App\Billing;

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
