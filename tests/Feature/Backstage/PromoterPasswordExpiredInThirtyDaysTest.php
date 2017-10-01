<?php

namespace Tests\Feature\Backstage;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PromoterPasswordExpiredInThirtyDaysTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_will_not_see_the_password_expired_if_they_are_valid()
    {
        $this->withExceptionHandling();
        $user = factory(User::class)->create(['password_changed_at'=>Carbon::parse('1 days ago')]);
        //$this->signIn($user);
        //dd($user->fresh()->password_changed_at);
        $response = $this->actingAs($user->fresh())->get('/backstage/concerts');
        $response->assertStatus(200);
    }

    /** @test */
    public function expired_user_will_see_exired_page()
    {
        $this->withExceptionHandling();
        $user = factory(User::class)->create(['password_changed_at'=>Carbon::parse('31 days ago')]);
        //$this->signIn($user);
        //dd($user->fresh()->password_changed_at);
        $response = $this->actingAs($user->fresh())->get('/backstage/concerts');
        $response->assertRedirect('/backstage/password/expired');
        //$response->assertViewIs('Auth.Passwords.expired');
    }


    /** @test */
    public function it_shows_the_expired_page()
    {
        //$this->withExceptionHandling();
        $user = factory(User::class)->create(['password_changed_at'=>Carbon::parse('31 days ago')]);
        $response = $this->actingAs($user)->get('/backstage/password/expired');
        //$response->assertRedirect('/backstage/password/expired');
        $response->assertViewIs('Auth.Passwords.expired');

    }

}
