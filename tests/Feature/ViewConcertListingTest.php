<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;
    /** @test */
    public function user_can_view_a_published_concert_listing()
    {
        //Arrange
        //create a concert
        $concert = factory(Concert::class)->states('published')->create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosty and Lethargy',
            'date' => Carbon::parse('December 15, 2016 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zipcode' => '17916',
            'additional_information' => 'For tickets call (555) 555-5555',
        ]);

        //Act
        //view the concert listing
        //dd($concert);
        $response = $this->get('/concerts/'.$concert->id);
        //Assert
        // see the concert details
        $response->assertSee('The Red Chord');
        $response->assertSee('with Animosty and Lethargy');
        $response->assertSee('December 15, 2016');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('The Mosh Pit');
        $response->assertSee('123 example Lane');
        $response->assertSee('Laraville');
        $response->assertSee('ON 17916');
        $response->assertSee('For tickets call (555) 555-5555');
    }

    /** @test */
    public function user_cannot_view_unpublished_concert_listings()
    {
        //Create a concert with a known date
        $this->withExceptionHandling();
        $concert = factory(Concert::class)->states('unpublished')->create();

         //view the concert listing
        $response = $this->get('/concerts/'.$concert->id);

        $response->assertStatus(404);

    }
}
