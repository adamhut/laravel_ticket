<?php

namespace Tests\Feature\Backstage;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PromoterLoginTest extends TestCase
{
	use DatabaseMigrations;
    

    /** @test */
     public function logging_in_successfully()
     {
         $user = factory(User::class)->create([
             'email' => 'jane@example.com',
             'password' => bcrypt('super-secret-password'),
         ]);
 
         $this->browse(function (Browser $browser) {
             $browser->visit('/login')
                    ->type('email', 'jane@example.com')
                    ->type('password', 'super-secret-password')
                    ->press('Log in')
                    ->assertPathIs('/backstage/concerts');
          });
      }
    /** @test */
    public function loggin_in_with_valid_credentals()
    {
    	//$this->disable
        $user = factory(User::class)->create([
        	'email' => 'jane@example.com',
        	'password' => bcrypt('super-secret-password'),
        ]);

        $response = $this->post('login',[
        	'email' => 'jane@example.com',
        	'password' => 'super-secret-password'
        ]);

        //$response->assertRedirect('/backstage/concerts/new');

        $response->assertRedirect('/backstage/concerts');

        $this->assertTrue(Auth::check());

        $this->assertTrue(Auth::user()->is($user)); 

    }


    /** @test */
    public function loggin_in_with_invalid_credentals()
    {
    	//$this->disable
        $user = factory(User::class)->create([
        	'email' => 'jane@example.com',
        	'password' => bcrypt('super-secret-password'),
        ]);

        $response = $this->post('login',[
        	'email' => 'jane@example.com',
        	'password' => 'wrong-password'
        ]);

        $response->assertRedirect('/login');

        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->hasOldInput('email'));
        $this->assertFalse(session()->hasOldInput('password'));

        $this->assertFalse(Auth::check());


    }
    /** @test */
    public function loggin_in_with_an_account_that_does_not_exist()
    {
    	//$this->disable

        $response = $this->post('login',[
        	'email' => 'nobody@example.com',
        	'password' => 'wrong-password'
        ]);

        $response->assertRedirect('/login');

        $response->assertSessionHasErrors('email');

        $this->assertFalse(Auth::check());
    }


    /** @test */
    public function loggin_out_the_current_user()
    {
        Auth::login(factory(User::class)->create());

        $response = $this->post('/logout');
        $response->assertRedirect('/login');

        $this->assertFalse(Auth::check());
    }
}
