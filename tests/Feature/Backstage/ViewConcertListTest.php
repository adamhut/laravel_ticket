<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Tests\TestCase;
use PHPUnit\Framework\Assert;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewConcertListTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp()
	{
		parent::setUp();

		
		
	}
    /** @test */
    public function guess_can_not_view_a_promoters_concert_list()
    {
    	$this->withExceptionHandling();
        $response = $this->get('/backstage/concerts');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function prmoters_can_only_view_a_list_of_their_own_concerts()
    {
    	$this->withExceptionHandling();

        $user = factory(User::class)->create();

        $otherUser = factory(User::class)->create();

        $concertA = factory(Concert::class)->create(['user_id' => $user->id]);
        $concertB = factory(Concert::class)->create(['user_id' => $user->id]);
        $concertC = factory(Concert::class)->create(['user_id' => $otherUser->id]);
        $concertD = factory(Concert::class)->create(['user_id' => $user->id]);

        //dd($concerts);
        $response = $this->actingAs($user)->get('/backstage/concerts');
        //dd($response->original);
        $response->assertStatus(200);
        //dd($response->original->getData());
        
        //$this->assertTrue($response->original->getData()['concerts']->contains($concertA));
        //change the marco
        $this->assertTrue($response->data('concerts')->contains($concertA));
        //then chagee to collection macro
        $response->data('concerts')->assertContains($concertA);
        $response->data('concerts')->assertContains($concertB);
        $response->data('concerts')->assertContains($concertD);
        $response->data('concerts')->assertNotContains($concertC);

        //$this->assertTrue($response->data('concerts')->contains($concertB));
        //$this->assertTrue($response->data('concerts')->contains($concertD));
        //$this->assertFalse($response->data('concerts')->contains($concertC));
    }



}
