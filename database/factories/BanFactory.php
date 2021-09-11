<?php

namespace Database\Factories;

use App\Models\Ban;
use App\Models\Wiki;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ban::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $unixTimestamp = $this->faker->boolean ? 0 : $this->faker->numberBetween(0, 60 * 60 * 24 * 30) + Carbon::now()->getTimestamp();
        return [
            'target' => $this->faker->lastName,
            'expiry' => Carbon::createFromTimestamp($unixTimestamp)->format('Y-m-d H:i:s'),
            'reason' => $this->faker->sentence,
            'ip' => 0,
            'is_protected' => $this->faker->boolean(30),
            'is_active' => $this->faker->boolean(80),
            'wiki_id' => $this->faker->boolean ? null : $this->faker->numberBetween(1, Wiki::count()),
        ];
    }

    /**
     * Set this as an IP ban.
     *
     * @return Factory
     */
    public function setIP()
    {
        return $this->state(function (array $attributes) {
            return [
                'ip' => 1,
                'target' => $this->faker->ipv4 . ($this->faker->boolean ? '/' . $this->faker->numberBetween(16, 30) : ''),
            ];
        });
    }
}
