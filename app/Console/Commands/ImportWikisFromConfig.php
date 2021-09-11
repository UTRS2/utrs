<?php

namespace App\Console\Commands;

use App\Models\Wiki;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use App\Services\MediaWiki\Api\MediaWikiRepository;

class ImportWikisFromConfig extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'utrs-maintenance:sync-wikis-to-database {--force : Skip all confirmation prompts in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all changes in config/wikis.php to database';

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
            /** @var Wiki $wiki */
            $wiki = Wiki::firstOrNew(['database_name' => $databaseName]);
            $this->line("Checking for changes in wiki $databaseName" . ($wiki->exists ? (" (#$wiki->id)") : ''));
            $wiki->display_name = $repo->getTargetProperty($databaseName, 'name');
            $wiki->is_accepting_appeals = !$repo->getTargetProperty($databaseName, 'hidden_from_appeal_wiki_list', false);

            if (!$wiki->exists || $wiki->isDirty()) {
                $prompt = $wiki->exists
                    ? "About to save changes in wiki $databaseName (#$wiki->id)"
                    : "About to create wiki $databaseName";

                if ($this->confirmToProceed($prompt)) {
                    $wiki->save();
                }
            }
        }

        $this->info('Deleting wikis not present in the configuration file anymore...');
        $deleted = Wiki::whereNotIn('database_name', $all)
            ->get();
        foreach ($deleted as $wiki) {
            /** @var Wiki $wiki */
            $this->line("Removing wiki $wiki->database_name (#$wiki->id)");

            if ($this->confirmToProceed("About to remove wiki $wiki->database_name (#$wiki->id) and all settings in it")) {
                $wiki->delete();
            }
        }

        $this->info('Done!');

        return 0;
    }
}
