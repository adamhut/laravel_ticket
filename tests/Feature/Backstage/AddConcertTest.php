<?php

namespace Tests\Feature\Backstage;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AddConcertTest extends TestCase
{
	use DatabaseMigrations;
    
    /** @test */
    public function promoters_can_view_the_add_concert_form()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(200);
    }

   	/** @test */
    public function guests_can_not_view_the_add_concert_form()
    {
        $this->withExceptionHandling();
        $response = $this->get('/backstage/concerts/new');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
