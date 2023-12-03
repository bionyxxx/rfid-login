<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'dutta@gmail.com',
            'card_uid' => 'E3 5E E3 11',
        ], [
            'name' => 'Dutta Fachrezy',
            'password' => bcrypt('password'),
        ]);
    }
}
