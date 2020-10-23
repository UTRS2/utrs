<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Ban;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Ban::class, function (Faker $faker) {
    $unixTimestamp = $faker->boolean ? 0 : $faker->numberBetween(0, 60 * 60 * 24 * 30) + Carbon::now()->getTimestamp();

    return [
        'target' => $faker->name,
        'expiry' => Carbon::createFromTimestamp($unixTimestamp)->format('Y-m-d H:i:s'),
        'reason' => $faker->sentence,
        'ip' => 0,
        'is_protected' => $faker->boolean(30),
        'is_active' => $faker->boolean(80),
    ];
});

$factory->defineAs(Ban::class, 'ip', function (Faker $faker) {
    $unixTimestamp = $faker->boolean ? 0 : $faker->numberBetween(0, 60 * 60 * 24 * 30) + Carbon::now()->getTimestamp();

    return [
        'target' => $faker->ipv4 . ($faker->boolean ? '/' . $faker->numberBetween(16, 30) : ''),
        'expiry' => Carbon::createFromTimestamp($unixTimestamp)->format('Y-m-d H:i:s'),
        'reason' => $faker->sentence,
        'ip' => 1,
        'is_protected' => $faker->boolean(30),
        'is_active' => $faker->boolean(80),
    ];
});
