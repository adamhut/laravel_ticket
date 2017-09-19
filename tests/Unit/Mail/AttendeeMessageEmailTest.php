<?php

namespace Tests\Unit\Mail;

use Tests\TestCase;
use App\AttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AttendeeMessageEmailTest extends TestCase
{
    /** @test */
    function email_has_the_correct_subject_and_message()
    {
        $message = new AttendeeMessage([
            'subject' => 'My subject',
            'message' => 'My message',
        ]);
        $email = new AttendeeMessageEmail($message);
        $this->assertEquals("My subject", $email->build()->subject);
        $this->assertEquals("My message", trim($this->render($email)));
    }

    /** @test */
    public function it_sends_the_Attendee_message_email()
    {
        Mail::fake();
        $message = new AttendeeMessage([
            'subject' => 'My subject',
            'message' => 'My message',
        ]);
        $recipient='bob@example.com';
        $email = new AttendeeMessageEmail($message);   
        Mail::to($recipient)->send($email); 
        Mail::assertSent(AttendeeMessageEmail::class,function($mail) use ($message){
            return $mail->hasTo('bob@example.com') && $mail->attendeeMessage->is($message);
        });
    }

    /** @test */
    public function it_queue_the_attendee_message_email()
    {
        Mail::fake();
        $message = new AttendeeMessage([
            'subject' => 'My subject',
            'message' => 'My message',
        ]);
        $recipient='bob@example.com';
        $email = new AttendeeMessageEmail($message);   
        Mail::to($recipient)->queue($email); 
        Mail::assertQueued(AttendeeMessageEmail::class,function($mail) use ($message){
            return $mail->hasTo('bob@example.com') && $mail->attendeeMessage->is($message);
        });
    }

    private function render($mailable)
    {
        $mailable->build();
        //dd($mailable->build());
        return view($mailable->textView, $mailable->buildViewData())->render();
    }
}
