<?php

namespace Tests\Unit\Http\Middleware;

use App\User;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ForceStripeAccount;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForceStripeAccountTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function users_without_a_stripe_account_are_force_to_connect_with_stripe()
    {
        $user = factory(User::class)->create([
            'stripe_account_id'=>null,
        ]);

        $this->be($user);

        $middleware = new ForceStripeAccount;


        $response = $middleware->handle(new Request,function($request){
            $this->fail("Next Middleware was called when it should not have been.");
        });

        $this->assertInstanceOf(RedirectResponse::class,$response);
        $this->assertEquals(route('backstage.stripe-connect.connect'),$response->getTargetUrl());

    }

    /** @test */
    public function users_with_a_stripe_account_can_continue()
    {
        $user = factory(User::class)->create([
            'stripe_account_id' => 'valid_stripe_id_1234',
        ]);

        $this->be($user);

        $middleware = new ForceStripeAccount;

        $request = new Request;

        $next  = new class{    
            public $called = false;
            public function __invoke($request)
            {
                $this->called= true;
                return $request;
            }
        };

        $response = $middleware->handle($request,$next);

        $this->assertTrue($next->called);

        $this->assertSame($response,$request);
       
    }


    /** @test */
    public function middleware_is_applied_to_all_backstage_routes()
    {
        $routes=[
            'backstage.concerts.index', 
            'backstage.concerts.new',
            'backstage.concerts.store',
            'backstage.concerts.edit',
            'backstage.concerts.update',
            'backstage.published-concerts.store',
            'backstage.published-concert-orders.index',
            'backstage.concert-messages.new',
            'backstage.concert-messages.store',
        ];

        foreach ($routes as $route) {
            //dd(Route::getRoutes()->getByName($route)->gatherMiddleware());
            $this->assertContains(
                'forceStripeAccount',
                Route::getRoutes()->getByName($route)->gatherMiddleware()
            );
          
        }


    }
}
