<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 11,
                'facility_id' => 5,
                'name' => 'B本部',
                'password' => '$2y$10$fDTRSs44KYcwACwH2ICPYujF8pEeW3LLhzQMsd1n8FHBwum9Vcox6',
                'email' => 'b_hq@example.com',
                'email_verified_at' => now(),
                'last_login_at' => now(),
                'remember_token' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
