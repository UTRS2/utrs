<?php

namespace App\Services\Version\Implementation;

use App\Services\Version\Api\Version;
use Exception;

class RealVersion implements Version
{
    /** @var string|null */
    private $version;

    public function getVersion(): string
    {
        if (!$this->version) {
            // TODO: when we do cache clearing when deploying, cache the version here
            try {
                $this->version = $this->tryDetermineVersion();
            } catch (Exception $ignored) {
                // ignore: we don't really care here, just return an empty string
                $this->version = '';
            }
        }

        return $this->version;
    }

    private function tryDetermineVersion(): string
    {
        if (file_exists(base_path('.git')) && function_exists('exec')) {
            $gitTag = htmlspecialchars(trim(exec('git describe --tags --abbrev=0')));
            $gitRevision = htmlspecialchars(trim(exec('git rev-parse --short HEAD')));

            if (strlen($gitTag) > 0) {
                return " (<a href=\"https://github.com/UTRS2/utrs/releases/tag/$gitTag\">$gitTag</a><!-- revision: $gitRevision -->)";
            }

            if (strlen($gitRevision) > 0) {
                return " (git-<a href=\"https://github.com/utrs2/utrs/commit/$gitRevision\">$gitRevision</a>)";
            }
        }

        return '';
    }
}
