<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [
                'user_group_id' => 2,
                'code' => '',
                'name' => '管理者',
                'function_id' => 1,
                'type' => 0,
                'level' => 2,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),

            ],
            [
                'user_group_id' => 2,
                'code' => '',
                'name' => '管理者',
                'function_id' => 2,
                'type' => 0,
                'level' => 2,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                'user_group_id' => 5,
                'code' => '',
                'name' => '管理者',
                'function_id' => 1,
                'type' => 0,
                'level' => 2,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
                [
                'user_group_id' => 5,
                'code' => '',
                'name' => '管理者',
                'function_id' => 2,
                'type' => 0,
                'level' => 2,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'user_group_id' => 6,
                'code' => '',
                'name' => '管理者',
                'function_id' => 1,
                'type' => 0,
                'level' => 2,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
                [
                'user_group_id' => 6,
                'code' => '',
                'name' => '管理者',
                'function_id' => 2,
                'type' => 0,
                'level' => 2,
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

        ]);
    }
}
