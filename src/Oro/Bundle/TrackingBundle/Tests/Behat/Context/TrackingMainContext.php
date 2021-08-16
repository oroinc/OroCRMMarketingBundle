<?php

namespace Oro\Bundle\TrackingBundle\Tests\Behat\Context;

use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

class TrackingMainContext extends OroFeatureContext
{
    const TRACKING_FILENAME_KEY = 'tracking';

    /**
     * Removes "var/logs/tracking/settings.ser" file which is generated on tracking configuration save
     * This prevents outdated configuration taken from this file in test
     *
     * Example: Given I reset tracking settings file
     *
     * @When /^(?:|I )reset tracking settings file$/
     */
    public function removeTrackingSettingsFile()
    {
        $filePath = $this->getAppKernel()->getProjectDir() . '/var/logs/tracking/settings.ser';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * This step used for generating static HTML page and add tracking code to it
     * It used in case when we need to check tracking data
     *
     * Example: Given I generate html page with tracking code from website "default"
     *
     * @When /^(?:|I )generate html page with tracking code from website "(?P<identifier>\w+)"$/
     *
     * @param string $identifier
     */
    public function generateHtmlPageWithTrackingCode($identifier)
    {
        // the public path where the generated page can be requested by direct link.
        $filePath = $this->getAppKernel()->getProjectDir() . '/public/media/' . $this->getHtmlFilename($identifier);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $website = $this->getRepository(TrackingWebsite::class)->findOneBy(['identifier' => $identifier]);
        self::assertNotNull($website, sprintf('Could not found tracking website "%s",', $identifier));

        $twig = $this->getAppContainer()->get('twig');
        $trackingCode = $twig->render('@OroTracking/TrackingWebsite/script.js.twig', ['entity' => $website]);
        $url = $this->getMinkParameter('base_url');
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $url = str_replace($scheme . '://', '', $url);

        $trackingCode = str_replace(
            ['[user_identifier]', '[host]'],
            ['"testUserId"', sprintf('"%s"', trim($url, '/'))],
            $trackingCode
        );

        $filesystem = $this->getAppContainer()->get('filesystem');

        $filesystem->dumpFile(
            $filePath,
            $this->getHtmlContent($trackingCode)
        );
    }

    /**
     * Example: Given I open html page with tracking code for website "default"
     *
     * @When /^(?:|I )open html page with tracking code for website "(?P<identifier>\w+)"$/
     *
     * @param string $identifier
     */
    public function openPageWithTrackingCode($identifier)
    {
        // the public path where the generated page can be requested by direct link.
        $filePath = '/media/' . $this->getHtmlFilename($identifier);

        $this->visitPath($filePath);
    }

    /**
     * @param string $className
     *
     * @return ObjectRepository
     */
    private function getRepository($className)
    {
        return $this->getAppContainer()
            ->get('doctrine')
            ->getManagerForClass($className)
            ->getRepository($className);
    }

    /**
     * @param string $identifier
     *
     * @return string
     */
    private function getHtmlFilename($identifier)
    {
        return sprintf('%s_%s.html', self::TRACKING_FILENAME_KEY, $identifier);
    }

    /**
     * @param string $trackingCode
     *
     * @return string
     */
    private function getHtmlContent($trackingCode)
    {
        $html = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        $trackingCode
    </head>
    <body></body>
</html>
HTML;

        return $html;
    }
}
