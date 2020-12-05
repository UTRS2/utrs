<?php

namespace Database\Factories;

use App\Models\Wiki;
use Illuminate\Database\Eloquent\Factories\Factory;

class WikiFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Wiki::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $languageCode = $this->faker->languageCode;
        return [
            'database_name' => $languageCode . 'wiki',
            'display_name' => $languageCode . ' Wikipedia',
            'is_accepting_appeals' => $this->faker->boolean(90),
        ];
    }
}
