<?php

namespace Database\Seeders;

use App\Models\AdministratorModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdministratorModel::create([
            'firstname' => 'Steven',
            'lastname' => 'Lopere',
            'email' => 'admin@super.com',
            'password' => bcrypt('password123'),
     
        ]);
    }
}
