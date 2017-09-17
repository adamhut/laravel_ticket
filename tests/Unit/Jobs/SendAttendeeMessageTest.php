<?php

namespace Tests\Unit\Jobs;

use OrderFactory;
use ConcertFactory;
use Tests\TestCase;
use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SendAttendeeMessageTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    public function it_send_message_to_all_concert_attendees()
    {
        $this->withExceptionHandling();
        Mail::fake();
        $concert = ConcertFactory::createPublished();
        $otherConcert = ConcertFactory::createPublished();

        $message = AttendeeMessage::create([
            'concert_id' => $concert->id,
            'subject' => 'My Subject',
            'message' => 'My Message',
        ]);

        $otherOrder = OrderFactory::createForConcert($otherConcert,['email'=>'jane@example.com']);
        $orderA = OrderFactory::createForConcert($concert,['email'=>'alex@example.com']);;
        $orderB = OrderFactory::createForConcert($concert,['email'=>'bob@example.com']);
        $orderC = OrderFactory::createForConcert($concert,['email'=>'cat@example.com']);

        SendAttendeeMessage::dispatch($message);

        Mail::assertSent(AttendeeMessageEmail::class,function($mail) use ($message){
            return $mail->hasTo('alex@example.com') && $mail->attendeeMessage->is($message);
        });

        Mail::assertSent(AttendeeMessageEmail::class,function($mail) use ($message){
            return $mail->hasTo('bob@example.com')&& $mail->attendeeMessage->is($message);
        });

        Mail::assertSent(AttendeeMessageEmail::class,function($mail) use ($message){
            return $mail->hasTo('cat@example.com')&& $mail->attendeeMessage->is($message);
        });
        Mail::assertNotSent(AttendeeMessageEmail::class,function($mail){
            return $mail->hasTo('jane@example.com');
        });
    }
}
