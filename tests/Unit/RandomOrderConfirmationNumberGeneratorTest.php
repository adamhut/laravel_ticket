<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\RandomOrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RandomOrderConfirmationNumberGeneratorTest extends TestCase
{
	//must be 24 characters long
	//can only contain uppercase letters and numbers
	//can not contain ambiguous characters
	//All order number must be unique
	//ABCDEFGHJKLMNPQRSTUVWXYZ
	//23456789
	
    /** @test */
    public function must_be_24_characters_long()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;
        $confirmationNumber = $generator->generate();
        $this->assertEquals(24, strlen($confirmationNumber));
    }

    /** @test */
    public function can_only_contain_uppercase_and_numbers()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumber = $generator->generate();

        $this->assertRegExp('/^[0-9A-Z]+$/', $confirmationNumber);

    }

    /** @test */
    public function can_not_contain_ambiguous_characters()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumber = $generator->generate();

        $this->assertFalse(strpos($confirmationNumber,'1'));
        $this->assertFalse(strpos($confirmationNumber,'I'));
        $this->assertFalse(strpos($confirmationNumber,'0'));
        $this->assertFalse(strpos($confirmationNumber,'O'));
    }

    /** @test */
    public function confirmation_numbers_must_be_unique()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumbers = array_map(function($i) use($generator){
        	return $generator->generate();
        },range(1,100));

        $this->assertCount(100,array_unique($confirmationNumbers));
    }
}
