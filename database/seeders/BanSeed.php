<?php

namespace Database\Seeders;

use App\Models\Ban;
use Illuminate\Database\Seeder;

class BanSeed extends Seeder
{
    public function run()
    {
        Ban::factory(8)
            ->create();

        Ban::factory(5)
            ->setIP()
            ->create();
    }
}
