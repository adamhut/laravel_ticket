<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use App\Invitation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;
    
    
    /** @test */
    public function viewing_an_unused_invitation()
    {
        $invitation  = factory(Invitation::class)->create([
            'user_id' => null,
            'code'  => 'TESTCODE1234',
        ]);
        $response  = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(200);

        $response->assertViewIs('invitations.show');

        $this->assertTrue($response->data('invitation')->is($invitation) );
    }

    /** @test */
    public function viewing_an_used_invitation()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create(),
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->get('/invitations/TESTCODE1234');
 
        $response->assertStatus(404);


    }

    /** @test */
    public function viewing_an_invitation_that_does_not_exist()
    {
        $response = $this->get('/invitations/TESTCODE1234');
        $response->assertStatus(404);
    }

    /** @test */
    public function registering_with_a_valid_invitation_code()
    {
        $this->withoutExceptionHandling();
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);
        $this->assertEquals(0, User::count());

        $response = $this->post('/register',[
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/backstage/concerts');

        $this->assertEquals(1,User::count());

        $user= User::first();

        $this->assertAuthenticatedAs($user);

        $this->assertEquals('john@example.com',$user->email);

        $this->assertTrue(Hash::check('secret', $user->password));

        $this->assertEquals($invitation->fresh()->user_id,$user->id);
        
        $this->assertTrue($invitation->fresh()->user->is($user));
    }

    /** @test */
    public function registering_with_a_used_invitation_code()
    {

        $invitation = factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create(),
            'code' => 'TESTCODE1234',
        ]);
        $this->assertEquals(1, User::count());

        $response = $this->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertStatus(404);

        $this->assertEquals(1, User::count());

    }

    /** @test */
    public function registering_with_a_invitation_code_that_does_not_exist()
    {

        $response = $this->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertStatus(404);

        $this->assertEquals(0, User::count());

    }


    /** @test */
    public function email_is_required()
    {
        //$this->withoutExceptionHandling();
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => '',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');

        $response->assertSessionHasErrors('email');

        $this->assertEquals(0, User::count());
    }

    /** @test */
    public function email_must_be_an_email()
    {
        //$this->withoutExceptionHandling();
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'not-an-email',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');

        $response->assertSessionHasErrors('email');

        $this->assertEquals(0, User::count());
    }

    /** @test */
    public function email_must_be_unique()
    {
        //$this->withoutExceptionHandling();
        $existingUser = factory(User::class)->create(['email'=>'john@example.com']);
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');

        $response->assertSessionHasErrors('email');

        $this->assertEquals(1, User::count());
    }

    /** @test */
    public function password_is_required()
    {
        //$this->withoutExceptionHandling();
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'john@example.com',
            'password' => '',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');

        $response->assertSessionHasErrors('password');

        $this->assertEquals(0, User::count());
    }
}

