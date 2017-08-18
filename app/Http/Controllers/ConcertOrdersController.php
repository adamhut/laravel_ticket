<?php

namespace App\Http\Controllers;

use App\Order;
use App\Concert;
use App\Reservation;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;

class ConcertOrdersController extends Controller
{
    protected $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);
        
        $this->validate(request(),[
            'email'=>['required','email'],
            'ticket_quantity' => ['required','integer','min:1'],
            'payment_token' => ['required']
        ]);

        try {
            //find some tickets
            $reservation = $concert->reserveTickets(request('ticket_quantity'),request('email'));

            //Charge the customer for the tickets
            $this->paymentGateway->charge( $reservation->totalCost(),request('payment_token'));
            
            //create an order for those Tickets
            //$order = $concert->createOrder(request('email'),$tickets);
            $order  = Order::forTickets($reservation->tickets(),$reservation->email(), $reservation->totalCost());

            return response()->json($order,201);
        } catch (PaymentFailedException $e) {
            $reservation->cancel();
            return response()->json([],422);
        }catch(NotEnoughTicketsException $e) {
            // /dd(request()->all());
            return response()->json([],422);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
