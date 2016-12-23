<?php

namespace Oro\Bundle\TrackingBundle\Migrations\Schema\v1_11;

use Psr\Log\LoggerInterface;

use Oro\Bundle\MigrationBundle\Migration\MigrationQuery;
use Oro\Bundle\TrackingBundle\Migration\TrackingScriptInstaller;

class InstallTrackingScriptQuery implements MigrationQuery
{
    /** @var string */
    private $webRootDir;

    /**
     * @param string $webRootDir
     */
    public function __construct($webRootDir)
    {
        $this->webRootDir = $webRootDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Copy a tracking script to application\'s web folder.';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $trackingScriptInstaller = new TrackingScriptInstaller($this->webRootDir, $logger);
        $trackingScriptInstaller->install();
    }
}
