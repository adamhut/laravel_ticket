<?php
namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\NullFile;
use Carbon\Carbon;
use App\Events\ConcertAdded;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertsController extends Controller
{

    public function index()
    {
        $publishedConcerts = Auth::user()->concerts->filter->isPublished();

        $unpublishedConcerts = Auth::user()->concerts->reject->isPublished();
       // / dd($publishedConcerts);
    	return view('backstage.concerts.index',[
            'publishedConcerts' =>$publishedConcerts,
            'unpublishedConcerts' =>$unpublishedConcerts
        ]);
    }

    public function create()
    {
        return view('backstage.concerts.create');
    }


    public function store()
    {

    	$this->validate(request(), [
            'title' => ['required'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:g:ia'],
            'venue' => ['required'],
            'venue_address' => ['required'],
            'city' => ['required'],
            'state' => ['required'],
            'zip' => ['required'],
            'ticket_price' => ['required', 'numeric', 'min:5'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'poster_image' => ['nullable','image',Rule::dimensions()->minWidth(400)->ratio(8.5/11)],
        ]);

    	$concert = Auth::user()->concerts()->create([
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
            //'poster_image_path' => request()->hasFile('poster_image') ? request('poster_image')->store('posters','s3'):'',
            'poster_image_path' =>  request('poster_image',new NullFile)->store('posters','s3'),
    	]);

        //Queue a job to process the poster image.
        ConcertAdded::dispatch($concert);
        //$concert->publish();

    	//return redirect()->route('concerts.show',$concert);
        return redirect()->route('backstage.concerts.index'); //
    }


    public function edit($id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);
        //dd($concert->isPublished());
        abort_if($concert->isPublished(), 403);

        return view('backstage.concerts.edit',[
            'concert' => $concert,
        ]);
    }


    public function update($id)
    {
        $concert= Auth::user()->concerts()->findOrFail($id);
        abort_if($concert->isPublished(), 403);

        $this->validate(request(), [
            'title' => ['required'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:g:ia'],
            'venue' => ['required'],
            'venue_address' => ['required'],
            'city' => ['required'],
            'state' => ['required'],
            'zip' => ['required'],
            'ticket_price' => ['required', 'numeric', 'min:5'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
        ]);

        // /dd($concert);

        $concert->update([
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
        ]);



        return redirect()->route('backstage.concerts.index');
    }
}
