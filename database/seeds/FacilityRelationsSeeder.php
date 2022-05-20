<?php

use Illuminate\Database\Seeder;

class FacilityRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('facility_relations')->insert([
            [
                'facility_id' => 3,
                'parent_facility_id' => '2',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'facility_id' => 4,
                'parent_facility_id' => '2',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
