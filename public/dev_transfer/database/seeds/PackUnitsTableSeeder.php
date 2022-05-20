<?php

use Illuminate\Database\Seeder;

class PackUnitsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('pack_units')->insert([
            [
                'medicine_id' => '1',
                'hot_code' => 'hot000000001',
                'jan_code' => 'jan000000001',
                'display_pack_unit' => '錠',
                'pack_count' => 10,
                'pack_unit' => '錠',
                'total_pack_count' => 10,
                'total_pack_unit' => '錠',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'medicine_id' => '2',
                'hot_code' => 'hot000000002',
                'jan_code' => 'jan000000002',
                'display_pack_unit' => '錠',
                'pack_count' => 10,
                'pack_unit' => '錠',
                'total_pack_count' => 10,
                'total_pack_unit' => '錠',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'medicine_id' => '3',
                'hot_code' => 'hot000000003',
                'jan_code' => 'jan000000003',
                'display_pack_unit' => '錠',
                'pack_count' => 10,
                'pack_unit' => '錠',
                'total_pack_count' => 10,
                'total_pack_unit' => '錠',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'medicine_id' => '4',
                'hot_code' => 'hot000000004',
                'jan_code' => 'jan000000004',
                'display_pack_unit' => '錠',
                'pack_count' => 10,
                'pack_unit' => '錠',
                'total_pack_count' => 10,
                'total_pack_unit' => '錠',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'medicine_id' => '5',
                'hot_code' => 'hot000000005',
                'jan_code' => 'jan000000005',
                'display_pack_unit' => '錠',
                'pack_count' => 10,
                'pack_unit' => '錠',
                'total_pack_count' => 10,
                'total_pack_unit' => '錠',
                'creater' => 0,
                'updater' => 0,
                'deleter' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
