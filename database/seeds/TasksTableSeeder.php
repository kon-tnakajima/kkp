<?php

use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tasks')->insert([
            [
                'name' => '未採用',
                'status' => '1',
                'actor_id' => '1',
                'apply_payment_flg' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '申請中',
                'status' => '2',
                'actor_id' => '2',
                'apply_payment_flg' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '交渉中',
                'status' => '3',
                'actor_id' => '3',
                'apply_payment_flg' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '承認待ち',
                'status' => '6',
                'actor_id' => '2',
                'apply_payment_flg' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '承認済',
                'status' => '7',
                'actor_id' => '1',
                'apply_payment_flg' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            /*
            [
                'name' => '採用可',
                'status' => '8',
                'actor_id' => '1',
                'apply_payment_flg' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => '採用禁止',
                'status' => '9',
                'actor_id' => '2',
                'apply_payment_flg' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
             */
            [
                'name' => '採用済み',
                'status' => '10',
                'actor_id' => '1',
                'apply_payment_flg' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
