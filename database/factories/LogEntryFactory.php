<?php

namespace Database\Factories;

use App\Models\LogEntry;
use App\Models\Appeal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LogEntry>
 */
class LogEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LogEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => -1,
            'model_type' => Appeal::class,
            'reason' => NULL,
            'action' => 'create',
            'ip' => $this->faker->ipv4,
            'ua' => $this->faker->userAgent,
            'protected' => 0,
        ];
    }
}
