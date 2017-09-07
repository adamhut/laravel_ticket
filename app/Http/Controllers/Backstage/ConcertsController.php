<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConcertsController extends Controller
{
    public function create()
    {
        return view('backstage.concerts.create');
    }


    public function store()
    {
    	$concert = Concert::create([
    		'user_id' =>auth()->user()->id,
    		'title' => request('title'),
    		'subtitle' => request('subtitle'),   		
            'additional_information' => request('additional_information'),
    		'date' => Carbon::parse(vsprintf('%s %s',[
    			request('date'),
    			request('time')
    		])),
    		'ticket_price' => request('ticket_price'),
    		'venue' => request('venue'),
            'venue_address' => request('venue_address'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'ticket_price' => request('ticket_price') * 100,
            'ticket_quantity' => (int) request('ticket_quantity'),    		
    	])->addTickets( request('ticket_quantity'));
    	return redirect()->route('concerts.show',$concert);
    }
}
