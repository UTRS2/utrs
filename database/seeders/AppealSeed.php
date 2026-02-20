<?php

namespace Database\Seeders;

use App\Models\Appeal;
use App\Models\Privatedata;
use App\Models\User;
use App\Models\LogEntry;
use Illuminate\Database\Seeder;

class AppealSeed extends Seeder
{
    public function run()
    {
        $first = Appeal::factory()
            ->create([ 'status' => Appeal::STATUS_DECLINE, ]);

        Appeal::factory()
            ->create([ 'appealfor' => $first->appealfor, ]);

        Appeal::factory(5)
            ->has(Privatedata::factory())
            ->create();

        $user = User::first();
        if ($user) {
            Appeal::factory(3)
                ->has(Privatedata::factory())
                ->create([ 'handlingadmin' => $user->id ]);
        }

        $otherUser = User::factory()->create();
        Appeal::factory(3)
            ->has(Privatedata::factory())
            ->create([ 'handlingadmin' => $otherUser->id ]);
    }
}
