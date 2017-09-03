<?php

namespace Tests\Unit;

use App\Ticket;
use Tests\TestCase;
use App\HashidsTicketCodeGenerator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HashidsTicketCodeGeneratorTest extends TestCase
{
    /** @test */
    public function ticket_code_are_at_least_6_characters_long()
    {
        $ticektCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

        //Act
        $code=$ticektCodeGenerator->generateFor(new Ticket(['id'=>1]));

        //Assert
        $this->assertTrue(strlen($code)==6);
    }

    /** @test */
    public function ticket_code_should_only_contain_upper_case_letters()
    {
        $ticektCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

        //Act
        $code= $ticektCodeGenerator->generateFor(new Ticket(['id'=>1]));

        //Assert
        $this->assertRegExp('/^[A-Z]+$/',$code);
    }

    /** @test */
    public function ticket_code_for_the_ticket_id_are_the_same()
    {
        $ticektCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

        //Act 
        $code1= $ticektCodeGenerator->generateFor(new Ticket(['id'=>1]));
        $code2= $ticektCodeGenerator->generateFor(new Ticket(['id'=>1]));
        //Assert
        $this->assertEquals($code1, $code2);
        //$this->assertRegExp('/^[A-Z]+$/',$code);
    }

    /** @test */
    public function ticket_code_for_different_ticket_id_are_different()
    {
        $ticektCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

        //Act 
        $code1= $ticektCodeGenerator->generateFor(new Ticket(['id'=>1]));
        $code2= $ticektCodeGenerator->generateFor(new Ticket(['id'=>2]));
        //Assert
        $this->assertNotEquals($code1, $code2);
       // $this->assertRegExp('/^[A-Z]+$/',$code);
    }

    /** @test */
    public function ticket_code_generate_with_different_sale_are_different()
    {
        $ticektCodeGenerator1 = new HashidsTicketCodeGenerator('testsalt1');
        $ticektCodeGenerator2 = new HashidsTicketCodeGenerator('testsalt2');

        //Act 
        $code1= $ticektCodeGenerator1->generateFor(new Ticket(['id'=>1]));
        $code2= $ticektCodeGenerator2->generateFor(new Ticket(['id'=>1]));
        //Assert
        $this->assertNotEquals($code1, $code2);
       // $this->assertRegExp('/^[A-Z]+$/',$code);
    }
}
