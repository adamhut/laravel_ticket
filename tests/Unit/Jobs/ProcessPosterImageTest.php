<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\ProcessPosterImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

	/** @test */
	public function it_resize_the_poster_image_to_600px_wide()
	{
		Storage::fake('public');

		Storage::disk('public')->put(
			'posters/example-poster.png',
			file_get_contents(base_path('tests/__fixtures__/full-size-poster.png'))
		);

	    $concert = \ConcertFactory::createUnpublished([
	    	'poster_image_path'=> 'posters/example-poster.png',
	    ]);

	    ProcessPosterImage::dispatch($concert);

	    $resizedImageContent = Storage::disk('public')->get('posters/example-poster.png');
	    list($width,$height) = getimagesizefromstring($resizedImageContent);    
	    $this->assertEquals(600,$width);
	    $this->assertEquals(776,$height);

	    $resizedImageContent =Storage::disk('public')->get('posters/example-poster.png');
	    $controlImageContent = file_get_contents(base_path('tests/__fixtures__/small-unoptimized-poster.png'));
	    $this->assertEquals($controlImageContent,$resizedImageContent);


	}

	/** @test */
	public function it_optimizes_the_poster_image()
	{
		Storage::fake('public');

		Storage::disk('public')->put(
			'posters/example-poster.png',
			file_get_contents(base_path('tests/__fixtures__/small-unoptimized-poster.png'))
		);

	    $concert = \ConcertFactory::createUnpublished([
	    	'poster_image_path'=> 'posters/example-poster.png',
	    ]);

	    ProcessPosterImage::dispatch($concert);

	    $optimizedImageSize = Storage::disk('public')->size('posters/example-poster.png');

	    //list($width,$height) = getimagesizefromstring($optimizedImageSize);   
	    // /dd($width);	    
	    $orginalSize = $filesize(base_path('tests/__fixtures__/small-unoptimized-poster.png'));

	    $this->assertLessThan($orginalSize,$optimizedImageSize); 
	    
	    $optimizedImageContent =Storage::disk('public')->get('posters/example-poster.png');

	    $controlImageContent = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));

	    $this->assertEquals($controlImageContent,$optimizedImageContent);

	}

}
