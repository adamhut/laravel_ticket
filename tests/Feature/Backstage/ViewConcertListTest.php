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

        $publishedConcertA = \ConcertFactory::createPublished(['user_id' => $user->id]);
        $publishedConcertB = \ConcertFactory::createPublished(['user_id' => $otherUser->id]);
        $publishedConcertC = \ConcertFactory::createPublished(['user_id' => $user->id]);
        //createUnpublished
        $unpublishedConcertA = \ConcertFactory::createUnpublished(['user_id' => $user->id]);
        $unpublishedConcertB = \ConcertFactory::createUnpublished(['user_id' => $otherUser->id]);
        $unpublishedConcertC = \ConcertFactory::createUnpublished(['user_id' => $user->id]);
        // /dd($unpublishedConcertA);

        $response = $this->actingAs($user)->get('/backstage/concerts');
        //dd($response->original);
        $response->assertStatus(200);
        //dd($response->original->getData());

        //$this->assertTrue($response->original->getData()['concerts']->contains($concertA));
        //change the marco
        //$this->assertTrue($response->data('concerts')->contains($concertA));
        //then chagee to collection macro
        //dd($response->data('publishedConcerts'));
        $response->data('publishedConcerts')->assertEquals([
            $publishedConcertA,
            $publishedConcertC,
        ]);

        $response->data('unpublishedConcerts')->assertEquals([
            $unpublishedConcertA,
            $unpublishedConcertC,
        ]);

        //$response->data('publishedConcerts')->assertContains($publishedConcertA);
        //$response->data('publishedConcerts')->assertNotContains($publishedConcertB);
        //$response->data('publishedConcerts')->assertContains($publishedConcertC);
        //$response->data('publishedConcerts')->assertNotContains($unpublishedConcertA);
        //$response->data('publishedConcerts')->assertNotContains($unpublishedConcertB);
        //$response->data('publishedConcerts')->assertNotContains($unpublishedConcertC);
        //$response->data('unpublishedConcerts')->assertContains($unpublishedConcertA);
        //$response->data('unpublishedConcerts')->assertNotContains($unpublishedConcertB);
        //$response->data('unpublishedConcerts')->assertContains($unpublishedConcertC);
        //$response->data('unpublishedConcerts')->assertNotContains($publishedConcertA);
        //$response->data('unpublishedConcerts')->assertNotContains($publishedConcertB);
        //$response->data('unpublishedConcerts')->assertNotContains($publishedConcertC);

        //$this->assertTrue($response->data('concerts')->contains($concertB));
        //$this->assertTrue($response->data('concerts')->contains($concertD));
        //$this->assertFalse($response->data('concerts')->contains($concertC));
    }



}
