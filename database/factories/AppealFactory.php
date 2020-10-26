<?php

namespace Database\Factories;

use App\Appeal;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppealFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appeal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'appealfor' => $this->faker->firstName,
            'blocktype' => 1,
            'status' => Appeal::STATUS_OPEN,
            'blockfound' => 1,
            'blockingadmin' => $this->faker->firstName,
            'blockreason' => $this->faker->sentence,
            'submitted' => $this->faker->dateTimeBetween('-3 days', '-1 hour'),
            'appealsecretkey' => implode('', $this->faker->words()),
            'appealtext' => $this->faker->sentence,
            'wiki' => MediaWikiRepository::getSupportedTargets(false)[0],
            'user_verified' => 0,
        ];
    }
}
