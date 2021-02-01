<?php

namespace Database\Factories;

use App\Models\Appeal;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

class TemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Template::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => implode(' ', $this->faker->words(3)),
            'template' => implode("\n\n", $this->faker->sentences(2)),
            'active' => $this->faker->boolean(80),
        ];
    }

    public function withStatusChange()
    {
        return $this->state(function (array $attributes) {
            return [
                'default_status' => $this->faker->randomElement(array_values(Appeal::REPLY_STATUS_CHANGE_OPTIONS)),
            ];
        });
    }
}
