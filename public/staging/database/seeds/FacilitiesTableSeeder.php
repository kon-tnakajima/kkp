<?php

use Illuminate\Database\Seeder;

class FacilitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('facilities')->insert([
            [
                'code' => '12345678901234567890',
                'name' => '文化連',
                'formal_name' => '日本文化厚生農業共同組合連合会',
                'actor_id' => 3,
                'zip' => '151-0053',
                'address' => '東京都渋谷区代々木2-5-5 新宿農協会館',
                'tel' => '03-3370-2541',
                'fax' => '03-0000-0000',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),

            ],
            [
                'code' => '12345678901234567890',
                'name' => '本部１',
                'formal_name' => '本部１',
                'actor_id' => 2,
                'zip' => '000-0000',
                'address' => '東京都中央区東日本橋',
                'tel' => '03-0000-0000',
                'fax' => '03-0000-0000',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => '12345678901234567890',
                'name' => '慶應義塾大学病院',
                'formal_name' => '慶應義塾大学病院',
                'actor_id' => 1,
                'zip' => '160-8582',
                'address' => '東京都新宿区信濃町35',
                'tel' => '03-3353-1211',
                'fax' => '03-3353-1212',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => '12345678901234567890',
                'name' => '慈恵医大',
                'formal_name' => '東京慈恵会医科大学附属病院',
                'actor_id' => 1,
                'zip' => '105-0003',
                'address' => '東京都港区西新橋3-19-18',
                'tel' => '03-3433-1111',
                'fax' => '03-0000-0000',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
