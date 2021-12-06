<?php

namespace Oro\Bundle\TrackingBundle\Tests\Functional\Command;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TrackingBundle\Entity\TrackingData;
use Oro\Component\Testing\TempDirExtension;
use Symfony\Component\Filesystem\Filesystem;

class ImportLogsCommandTest extends WebTestCase
{
    use TempDirExtension;

    /** @var Filesystem */
    private $fs;

    /** @var string */
    private $directory;

    /** @var string */
    private $logsDir;

    protected function setUp(): void
    {
        $this->initClient();
        $this->fs = new Filesystem();
        $this->directory = $this->getTempDir('tracking', false);
    }

    public function testDirectoryEmpty()
    {
        $this->assertFalse($this->fs->exists($this->directory));

        $result = $this->runCommand(
            'oro:cron:import-tracking',
            ['--directory' => $this->directory]
        );
        $this->assertTrue($this->fs->exists($this->directory));
        self::assertStringContainsString('Logs not found', $result);
    }

    public function testFileProcessed()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $file = $date->modify('-1 day')->format('Ymd-H') . '-60-1.log';

        $this->fs->dumpFile(
            $this->directory . DIRECTORY_SEPARATOR . $file,
            json_encode(['prop' => 'value'], JSON_THROW_ON_ERROR)
        );

        $result = $this->runCommand(
            'oro:cron:import-tracking',
            ['--directory' => $this->directory]
        );
        self::assertFileDoesNotExist($this->directory . DIRECTORY_SEPARATOR . $file);
        self::assertStringContainsString(sprintf('Successful: "%s"', $file), $result);
    }

    public function testCurrentFileNotProcessed()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $file = $date->format('Ymd-H') . '-60-1.log';

        $this->fs->dumpFile(
            $this->directory . DIRECTORY_SEPARATOR . $file,
            json_encode(['prop' => 'value'], JSON_THROW_ON_ERROR)
        );

        $result = $this->runCommand(
            'oro:cron:import-tracking',
            ['--directory' => $this->directory]
        );
        $this->assertFileExists($this->directory . DIRECTORY_SEPARATOR . $file);
        self::assertStringNotContainsString(sprintf('Successful: "%s"', $file), $result);
    }

    public function testIsFileProcessed()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $fileName = $date->modify('-1 day')->format('Ymd-H') . '-60-1.log';
        $file = $this->directory . DIRECTORY_SEPARATOR . $fileName;

        $this->fs->dumpFile($file, json_encode(['prop' => 'value'], JSON_THROW_ON_ERROR));

        $jobResult = $this->getContainer()->get('oro_importexport.job_executor')->executeJob(
            ProcessorRegistry::TYPE_IMPORT,
            'import_log_to_database',
            [
                ProcessorRegistry::TYPE_IMPORT => [
                    'entityName' => TrackingData::class,
                    'processorAlias' => 'oro_tracking.processor.data',
                    'file' => $file,
                ],
            ]
        );
        $this->assertTrue($jobResult->isSuccessful());

        $result = $this->runCommand(
            'oro:cron:import-tracking',
            ['--directory' => $this->directory]
        );
        self::assertFileDoesNotExist($this->directory . DIRECTORY_SEPARATOR . $file);
        self::assertStringContainsString(sprintf('"%s" already processed', $fileName), $result);
    }
}
