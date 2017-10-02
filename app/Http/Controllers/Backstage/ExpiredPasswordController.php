<?php

namespace App\Http\Controllers\Backstage;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ExpiredPasswordController extends Controller
{
    //
    public function index()
    {
        $user = auth()->user();
        //dd($request->all());
        return view('Auth.Passwords.expired',compact('user'));
    }

    public function store(Request $request)
    {
        // /dd($request);
        $data = $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        if (!Hash::check($data['current_password'], $request->user()->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is not correct']);
        }
        $request->user()->update([
            'password' => bcrypt($data['password']),
            'password_changed_at' => Carbon::now()->toDateTimeString(),
        ]);
        return redirect()->route('backstage.concerts.index')->with(['status' => 'Password changed successfully']);

    }
}
