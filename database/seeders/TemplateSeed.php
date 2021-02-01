<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeed extends Seeder
{
    public function run()
    {
        Template::factory(3)
            ->withStatusChange()
            ->create();

        Template::factory(1)
            ->withStatusChange()
            ->create();
    }
}
