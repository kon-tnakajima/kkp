<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ActorsTableSeeder::class,
            FacilitiesTableSeeder::class,
            FacilityRelationsSeeder::class,
            FunctionsTableSeeder::class,
            MedicinesTableSeeder::class,
            RolesTableSeeder::class,
            UserGroupRelationsTableSeeder::class,
            UserGroupsTableSeeder::class,
            UsersTableSeeder::class,
            PackUnitsTableSeeder::class,
            MakersTableSeeder::class,
            MedicinePricesTableSeeder::class,
            TasksTableSeeder::class,
        ]);
    }
}
