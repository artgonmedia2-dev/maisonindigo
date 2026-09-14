<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            SizeChartsSeeder::class,
            ShippingZonesSeeder::class,
            DemoCatalogSeeder::class,
            // Sprint 5 : PagesSeeder
        ]);
    }
}
