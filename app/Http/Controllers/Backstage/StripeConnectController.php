<?php

namespace App\Http\Controllers\Backstage;

use Zttp\Zttp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StripeConnectController extends Controller
{
    public function connect()
    {
        return view('backstage.stripe-connect.connect');
    }

    //
    public function authorizeRedirect()
    {
        $url = vsprintf('%s?%s',[
            'https://connect.stripe.com/oauth/authorize',
            http_build_query([
                'response_type'  => 'code',
                'scope' => 'read_write',
                'client_id' => config('services.stripe.client_id'),
            ]),
        ]);
        //dd($url);
        return redirect($url);
    }

    
    public function redirect()
    {
        $accessTokeResponse=Zttp::asFormParams()->post('https://connect.stripe.com/oauth/token',[
            'grant_type' => 'authorization_code',
            'code' => request('code'),
            'client_secret' => config('services.stripe.secret'),
        ])->json();

        //info($accessTokeResponse);
        Auth::user()->update([
            'stripe_account_id'=>$accessTokeResponse['stripe_user_id'],
            'stripe_access_token' => $accessTokeResponse['access_token'],
        ]);

        return redirect()->route('backstage.concerts.index');
    }
}
