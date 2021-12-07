<?php

use App\Models\Appeal;
use Illuminate\Database\Migrations\Migration;

class BackfillWikiIdsToAppeals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('appeals')
            ->select(['appeals.wiki', 'wikis.id'])->distinct()
            ->join('wikis', 'wikis.database_name', '=', 'appeals.wiki')
            ->get()
            ->each(function ($entry) {
                DB::table('appeals')
                    ->where('wiki', $entry->wiki)
                    ->update([
                        'wiki_id' => $entry->id,
                    ]);
            });

        if (Appeal::whereNull('wiki_id')->count() !== 0) {
            // Check just in case, before the next migration drops the database names
            throw new RuntimeException('Failed to backfill wiki_id to all appeals');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('appeals')
            ->update([
                'wiki_id' => null,
            ]);
    }
}
