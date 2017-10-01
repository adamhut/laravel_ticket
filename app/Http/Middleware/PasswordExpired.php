<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class PasswordExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        //return $next($request);
        if($user->isPasswordExpired())
        {
            return redirect()->route('password.expired');
        }

        return $next($request);
    }
}
