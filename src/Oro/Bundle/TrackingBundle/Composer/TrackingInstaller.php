<?php

namespace Oro\Bundle\TrackingBundle\Composer;

use Composer\Script\Event;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;
use Symfony\Component\Filesystem\Filesystem;

class TrackingInstaller extends ScriptHandler
{
    const SCRIPT_PATH = '/../Resources/lib';
    const SCRIPT_NAME = 'tracking.php';

    /**
     * Copied tracking script to application's web directory
     *
     * @param Event $event Composer script event
     */
    public static function copyTrackingScript(Event $event)
    {
        $options = self::getOptions($event);
        $webDir  = $options['symfony-web-dir'];

        $scriptPath = __DIR__ . sprintf('%s/%s', static::SCRIPT_PATH, static::SCRIPT_NAME);
        $targetPath = $webDir . sprintf('/%s', static::SCRIPT_NAME);

        $filesystem = new Filesystem();
        $filesystem->copy($scriptPath, $targetPath, true);
        $event->getIO()->write(
            sprintf('<info>Copied tracking script to "%s"</info>', $targetPath)
        );
    }
}
