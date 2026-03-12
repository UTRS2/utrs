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
            // when we do cache clearing when deploying, cache the version here
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
            // silence errors from git if unavailable
            $gitTag = htmlspecialchars(trim(exec('git describe --tags --abbrev=0 2>/dev/null')));
            $gitBranch = htmlspecialchars(trim(exec('git rev-parse --abbrev-ref HEAD 2>/dev/null')));
            $gitRevision8 = htmlspecialchars(trim(exec('git rev-parse --short=8 HEAD 2>/dev/null')));

            // prefer tag if present, but include branch and short (8 char) revision when possible
            if (strlen($gitTag) > 0) {
                $branchPart = ($gitBranch !== '' && $gitBranch !== 'HEAD') ? " on <a href=\"https://github.com/utrs2/utrs/tree/$gitBranch\">$gitBranch</a>" : '';
                $revPart = $gitRevision8 ? " <a href=\"https://github.com/utrs2/utrs/commit/$gitRevision8\">$gitRevision8</a>" : '';
                return " (<a href=\"https://github.com/utrs2/utrs/releases/tag/$gitTag\">$gitTag</a> {$branchPart} at revision {$revPart})";
            }

            if (strlen($gitRevision8) > 0) {
                $branchPart = ($gitBranch !== '' && $gitBranch !== 'HEAD') ? "branch <a href=\"https://github.com/utrs2/utrs/tree/$gitBranch\">$gitBranch</a> / " : '';
                return " ({$branchPart}<a href=\"https://github.com/utrs2/utrs/commit/$gitRevision8\">$gitRevision8</a>)";
            }
        }

        return '';
    }
}
