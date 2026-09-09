<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            // Sprint 1 : DemoCatalogSeeder, SizeChartsSeeder, ShippingZonesSeeder, PagesSeeder
        ]);
    }
}
