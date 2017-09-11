<?php

namespace Tests\Unit\Mail;

use App\Order;
use Tests\TestCase;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderConfirmationEmailTest extends TestCase
{
    /** @test */
    public function email_contain_a_link_to_order_confirmation_page()
    {
        $order = factory(Order::class)->make([
            'confirmation_number'=>"ORDERCONFIRMATION1234",
        ]);

        $email = new OrderConfirmationEmail($order);

        $rendered = $this->render($email);

        //In Laravel 5.5
        //$rendered = $email->render();

        $this->assertContains(url('/orders/ORDERCONFIRMATION1234'),$rendered);

    }

    /** @test */
    public function email_has_a_subject()
    {
        $order = factory(Order::class)->make();

        $email = new OrderConfirmationEmail($order);

        $this->assertEquals("Your TicketBeast Order", $email->build()->subject);
    }

    public function render($mailable)
    {
        $mailable->build();
        return view($mailable->view,$mailable->buildViewData())->render();
    }
}