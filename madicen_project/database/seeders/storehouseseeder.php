<?php

namespace Database\Seeders;

use App\Models\storehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class storehouseseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        storehouse::create([
            'name' => 'First storehouse',
            'phone' => '0934363768',
            'password' => '123455678',
        ]);

        storehouse::create([
            'name' => 'secound storehouse',
            'phone' => '0938156382',
            'password' => '1234556789',
        ]);
    }
}
