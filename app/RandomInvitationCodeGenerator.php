<?php 
namespace App;

class RandomInvitationCodeGenerator implements OrderConfirmationNumberGenerator,InvitationCodeGenerator
{
    protected $length = 24;

    public function generate()
    {
        $pool = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, $this->length)), 0, $this->length);
    }
}