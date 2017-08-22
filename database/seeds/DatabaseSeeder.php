<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
       // User::truncate();
       // Concert::truncate();
        $user = factory(App\User::class)->create([
            'email' => "adam@example.com",
            'password' => bcrypt('secret'),
        ]);
        factory(App\Concert::class)->create([
            'user_id' => $user->id,
            'title' => "Slayer",
            'subtitle' => "with Forbidden and Testament",
            'additional_information' => null,
            'venue' => "The Rock Pile",
            'venue_address' => "55 Sample Blvd",
            'city' => "Laraville",
            'state' => "ON",
            'zip' => "19276",
            
            'date' => Carbon::today()->addMonths(6)->hour(19),
            'ticket_price' => 5500,
            'ticket_quantity' => 10,
        ])->addTickets(10);
    }
}
