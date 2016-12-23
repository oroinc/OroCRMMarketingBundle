<?php

namespace Oro\Bundle\TrackingBundle\Migration;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class TrackingScriptInstaller
{
    const SCRIPT_PATH = '/../Resources/lib';
    const SCRIPT_NAME = 'tracking.php';

    /** @var string */
    private $webRootDir;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param string          $webRootDir
     * @param LoggerInterface $logger
     */
    public function __construct($webRootDir, LoggerInterface $logger)
    {
        $this->webRootDir = $webRootDir;
        $this->logger = $logger;
    }

    /**
     * Copied tracking script to application's web directory
     */
    public function install()
    {
        $scriptPath = __DIR__ . sprintf('%s/%s', static::SCRIPT_PATH, static::SCRIPT_NAME);
        $targetPath = $this->webRootDir . sprintf('/%s', static::SCRIPT_NAME);

        $filesystem = new Filesystem();
        $filesystem->copy($scriptPath, $targetPath, true);
        $this->logger->notice(
            sprintf('A tracking script has been copied to "%s"', $targetPath)
        );
    }
}
