<?php

namespace Database\Factories;

use App\Models\Appeal;
use App\Models\Privatedata;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrivateDataFactory extends Factory
{
    protected $model = Privatedata::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'appealID' => $this->faker->numberBetween(1, Appeal::count()),
            'ipaddress' => $this->faker->ipv4,
            'useragent' => $this->faker->userAgent,
            'language' => $this->faker->locale,
        ];
    }
}
