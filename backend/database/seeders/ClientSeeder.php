<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::insert([
            ['name' => 'Jan Kowalski', 'city' => 'Warsaw'],
            ['name' => 'Anna Nowak', 'city' => 'Kraków'],
            ['name' => 'Piotr Zieliński', 'city' => 'Gdańsk'],
        ]);
    }
}
