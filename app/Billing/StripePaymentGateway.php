<?php
namespace App\Billing;

use Stripe\Charge;


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
        $result = Charge::create([
            "amount" => $amount,
            "currency" => "usd",
            "source" => $token, // obtained with Stripe.js
            //"description" => "Charge for matthew.smith@example.com"
        ],$this->apiKey);

    }
}
