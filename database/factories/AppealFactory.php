<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Appeal;
use App\MwApi\MwApiUrls;
use Faker\Generator as Faker;

$factory->define(Appeal::class, function (Faker $faker) {
    return [
        'appealfor' => $faker->firstName,
        'privacylevel' => 0,
        'privacyreview' => null,
        'blocktype' => 1,
        'status' => Appeal::STATUS_OPEN,
        'blockfound' => 1,
        'blockingadmin' => $faker->firstName,
        'blockreason' => $faker->sentence,
        'submitted' => $faker->dateTimeBetween('-3 days', '-1 hour'),
        'appealsecretkey' => implode('', $faker->words()),
        'appealtext' => $faker->sentence,
        'wiki' => $faker->randomElement(MwApiUrls::getSupportedWikis()),
        'user_verified' => 0,
    ];
});
