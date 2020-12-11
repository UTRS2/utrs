<?php

namespace Database\Factories;

use App\Models\Appeal;
use App\Models\Privatedata;
use Illuminate\Database\Eloquent\Factories\Factory;
use Taavi\FakerAcceptLanguage\AcceptLanguageFakerProvider;

class PrivatedataFactory extends Factory
{
    protected $model = Privatedata::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $this->faker->addProvider(new AcceptLanguageFakerProvider($this->faker));

        return [
            'appealID' => $this->faker->numberBetween(1, Appeal::count()),
            'ipaddress' => $this->faker->ipv4,
            'useragent' => $this->faker->userAgent,
            'language' => $this->faker->acceptLanguage,
        ];
    }
}
