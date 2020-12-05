<?php

namespace App\Console\Commands;

use App\Models\Wiki;
use Illuminate\Console\Command;
use App\Services\MediaWiki\Api\MediaWikiRepository;

class ImportWikisFromConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'utrs-maintenance:sync-wikis-to-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all changes in config/wikis.php to database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $repo = app(MediaWikiRepository::class);
        $all = $repo->getSupportedTargets();

        $this->info('Syncronizing existing wikis...');
        foreach ($all as $databaseName) {
            $wiki = Wiki::firstOrNew(['database_name' => $databaseName]);
            $this->line("Updating wiki $databaseName" . ($wiki->exists ? (" (#$wiki->id)") : ''));
            $wiki->display_name = $repo->getTargetProperty($databaseName, 'name');
            $wiki->is_accepting_appeals = !$repo->getTargetProperty($databaseName, 'hidden_from_appeal_wiki_list', false);
            $wiki->save();
        }

        $this->info('Deleting wikis not present in the configuration file anymore...');
        $deleted = Wiki::whereNotIn('database_name', $all)
            ->get();
        foreach ($deleted as $wiki) {
            /** @var Wiki $wiki */
            $this->line("Removing wiki $wiki->database_name (#$wiki->id)");
            $wiki->delete();
        }

        $this->info('Done!');

        return 0;
    }
}
