<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed only the V1 catalogue. Never create users with default passwords.
        $this->call(GrantOpportunitySeeder::class);
    }
}
