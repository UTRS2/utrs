<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Template;
use Faker\Generator as Faker;

$factory->define(Template::class, function (Faker $faker) {
    return [
        'name' => implode(' ', $faker->words(3)),
        'template' => implode("\n\n", $faker->sentences(2)),
        'active' => $faker->boolean(80),
    ];
});
