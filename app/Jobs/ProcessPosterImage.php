<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Intervention\Image\Facades\Image;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessPosterImage implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $concert;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($concert)
    {
        //
        $this->concert = $concert;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $imageContent = Storage::disk('public')->get($concert->poster_image_path);

        $img = Image::make($imageContent);

        $image->resize(600,null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->limitColors(255)->encode();

        Storage::disk('public')->put($concert->poster_image_path,(string)$image);

    }
}
