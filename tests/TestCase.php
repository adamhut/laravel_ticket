<?php

namespace Tests;

use App\Exceptions\Handler;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp()
    {
        parent::setUp();
        //very use for 
        //Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        $this->disableExceptionHandling();

        TestResponse::macro('data',function($key){
            return $this->original->getData()['concerts'];
        });

        Collection::macro('assertContains',function($value){
            Assert::assertTrue($this->contains($value),'Failed asserting that the collection contained that specifify value');
        });

        Collection::macro('assertNotContains',function($value){
            Assert::assertFalse($this->contains($value),'Failed asserting that the collection did not contained that specifify value');
        });

    }

    protected function from($url)
    {
        session()->setPreviousUrl(url($url));       
        return $this;
    }

     // Hat tip, @adamwathan.
    protected function disableExceptionHandling()
    {
        $this->oldExceptionHandler = $this->app->make(ExceptionHandler::class);
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(\Exception $e) {}
            public function render($request, \Exception $e) {
                throw $e;
            }
        });
    }
    protected function withExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, $this->oldExceptionHandler);
        return $this;
    }
}
