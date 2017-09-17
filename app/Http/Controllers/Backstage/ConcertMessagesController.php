<?php

namespace App\Http\Controllers\Backstage;

use Illuminate\Http\Request;
use App\Jobs\SendAttendeeMessage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertMessagesController extends Controller
{
    public function create()
    {
        $concert = Auth::user()->concerts()->findOrFail($id);
        return view('backstage.concert-messages.new', ['concert' => $concert]);
    }
    //
    public function store($id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);
        $this->validate(request(), [
            'subject' => ['required'],
            'message' => ['required'],
        ]);

        $message =$concert->attendeeMessages()->create([
            'subject' => request('subject'),
            'message' => request('message'),
        ]);

        SendAttendeeMessage::dispatch($message);

        return redirect()->route('backstage.concert-messages.new', $concert)
            ->with('flash','You message Has Been Sent');
    }
}
