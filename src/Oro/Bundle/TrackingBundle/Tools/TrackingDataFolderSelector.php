<?php

namespace Oro\Bundle\TrackingBundle\Tools;

/**
 * Tracking data folder helper
 */
class TrackingDataFolderSelector
{
    public const DEFAULT_FOLDER = 'var/data/import_files/tracking';

    private string $projectDir;
    private ?string $envVar = null;

    public function __construct(string $projectDir, ?string $envVar = null)
    {
        $this->projectDir = $projectDir;
        $this->envVar = $envVar;
    }

    public function retrieve(): string
    {
        if ($this->envVar) {
            return $this->projectDir.DIRECTORY_SEPARATOR.$this->envVar;
        }

        return $this->projectDir.DIRECTORY_SEPARATOR.self::DEFAULT_FOLDER;
    }

    public static function retrieveForLogsDir(string $logsDir): string
    {
        return dirname($logsDir, 2).DIRECTORY_SEPARATOR.self::DEFAULT_FOLDER;
    }
}
