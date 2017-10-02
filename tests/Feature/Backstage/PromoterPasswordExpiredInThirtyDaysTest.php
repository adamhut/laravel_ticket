<?php

namespace Tests\Feature\Backstage;

use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PromoterPasswordExpiredInThirtyDaysTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_shows_the_expired_page()
    {
        //$this->withExceptionHandling();
        $user = factory(User::class)->create(['password_changed_at'=>Carbon::parse('31 days ago')]);
        $response = $this->actingAs($user)->get('/backstage/password/expired');
        //$response->assertRedirect('/backstage/password/expired');
        $response->assertViewIs('Auth.Passwords.expired');
    }

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
    public function user_will_not_see_the_password_expired_when_they_are_only_unchange_for_thirty_days()
    {
        $this->withExceptionHandling();
        $user = factory(User::class)->create(['password_changed_at'=>Carbon::parse('30 days ago')]);
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

        $response = $this->actingAs($user->fresh())->get('/backstage/concerts');

        $response->assertRedirect('/backstage/password/expired');
    }

    /** @test */
    public function it_redirect_back_to_backsatage_concerts_page_after_update_password()
    {
        $this->withExceptionHandling();

        $password='strong-password';

        $newPassword='new-strong-password';

        $user = factory(User::class)->create(['password_changed_at'=>Carbon::parse('35 days ago'),'password'=>bcrypt($password)]);
        //echo $user->password."\n";
        $response = $this->actingAs($user)->post('/backstage/password/expired',[
            'current_password' => $password,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);
        //$response->assertSessionHasError('password');
        //dd($user->fresh()->password);
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));

        $response->assertStatus(302);

        $response->assertRedirect('/backstage/concerts');

    }

     /** @test */
    public function it_redirect_back_to_expired_page_if_wrong_old_password()
    {
        $this->withExceptionHandling();

        $password='strong-password';

        $newPassword='new-strong-password';

        $user = factory(User::class)->create(['password_changed_at'=>Carbon::parse('35 days ago'),'password'=>bcrypt($password)]);
        //echo $user->password."\n";
        $response = $this->actingAs($user)->from('/backstage/password/expired')->post('/backstage/password/expired',[
            'current_password' => 'wrong-password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);
        $response->assertRedirect('/backstage/password/expired');
        $response->assertSessionHasErrors('current_password');
    }


}
