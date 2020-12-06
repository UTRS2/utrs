<?php

use Illuminate\Database\Migrations\Migration;

class BackfillWikiIdToTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $count = DB::table('templates')->count();
        if ($count == 0) {
            return;
        }

        $wikiId = DB::table('wikis')
            ->where('database_name', 'enwiki')
            ->pluck('id')
            ->first();
        if (!$wikiId) {
            throw new RuntimeException('Please synchronize wikis to the database before running this migration.');
        }

        DB::table('templates')
            ->update([
                'wiki_id' => $wikiId,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('templates')
            ->update([
                'wiki_id' => null,
            ]);
    }
}
