<?php

use Illuminate\Database\Seeder;

class UserGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_groups')->insert([
            [
                'name' => '内科',
                'facility_id' => 6,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),

            ],
            [
                'name' => '外科',
                'facility_id' => 6,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '内科',
                'facility_id' => 7,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),

            ],
            [
                'name' => '外科',
                'facility_id' => 8,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '本部',
                'facility_id' => 5,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

        ]);
    }
}
