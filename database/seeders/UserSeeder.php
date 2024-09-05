<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Developer',
                'email' => 'admin@yopmail.com',
                'mobile' => '1111111111',
                'password' => hash('sha512', 'admin'),
                'role' => 'admin'
            ],
            [
                'name' => 'Dev',
                'email' => 'konzept@yopmail.com',
                'mobile' => '2222222222',
                'password' => hash('sha512', 'admin'),
                'role' => 'admin'
            ],
            [
                'name' => 'Testing',
                'email' => 'test@yopmail.com',
                'mobile' => '3333333333',
                'password' => hash('sha512', 'admin'),
                'role' => 'admin'
            ],
        ];
        DB::table('users')->insert($users);
    }
}
