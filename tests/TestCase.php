<?php

namespace Tests;

use App\Exceptions\Handler;
use PHPUnit\Framework\Assert;
//use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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
        //$this->withoutExceptionHandling();

        /*
        TestResponse::macro('assertViewIs', function ($name) {
            Assert::assertEquals($name, $this->original->name());
        });
        */

        TestResponse::macro('data',function($key){
            return $this->original->getData()[$key];
        });

        EloquentCollection::macro('assertContains', function ($value) {
            Assert::assertTrue($this->contains($value), "Failed asserting that the collection contains the specified value.");
        });

        EloquentCollection::macro('assertNotContains', function ($value) {
            Assert::assertFalse($this->contains($value), "Failed asserting that the collection does not contain the specified value.");
        });

        EloquentCollection::macro('assertEquals', function($items){
            Assert::assertEquals(count($this),count($items));
            $this->zip($items)->each(function($pair){
                list($a,$b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });

    }
    /*
    protected function from($url)
    {
        session()->setPreviousUrl(url($url));
        return $this;
    }
    */

    // Hat tip, @adamwathan.
    protected function disableExceptionHandling()
    {
        //$this->withoutExceptionHandling();
        /*
        $this->oldExceptionHandler = $this->app->make(ExceptionHandler::class);
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(\Exception $e) {}
            public function render($request, \Exception $e) {
                throw $e;
            }
        });
        */
    }
    protected function withExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, $this->oldExceptionHandler);
        return $this;
    }
}
