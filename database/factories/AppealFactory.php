<?php

namespace Database\Factories;

use App\Models\Appeal;
use App\Models\Wiki;
use App\Models\LogEntry;
use App\Services\Facades\MediaWikiRepository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Database\Factories\LogEntryFactory;


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
        // first because many tests (sadly) assume that ://
        $wiki = Wiki::first();

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
            'wiki' => $wiki->database_name,
            'wiki_id' => $wiki->id,
            'user_verified' => 0,
            'proxy' => 0,
        ];
    }
}
