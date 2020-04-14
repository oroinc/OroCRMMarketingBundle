<?php

namespace Oro\Bundle\TrackingBundle\Tests\Functional\ImportExport;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\TrackingBundle\ImportExport\LogReader;
use Oro\Component\Testing\TempDirExtension;
use Symfony\Component\Filesystem\Filesystem;

class LogReaderTest extends \PHPUnit\Framework\TestCase
{
    use TempDirExtension;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextRegistry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $stepExecution;

    /**
     * @var LogReader
     */
    protected $reader;

    protected function setUp(): void
    {
        $this->contextRegistry = $this->createMock(ContextRegistry::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);

        $this->reader = new LogReader($this->contextRegistry);
    }

    /**
     * @return Filesystem
     */
    private function getFilesystem()
    {
        return new Filesystem();
    }

    public function testRead()
    {
        $data = [
            'name'  => 'event_name',
            'value' => 'done'
        ];

        $filename = $this->getTempDir('tracking') . DIRECTORY_SEPARATOR . 'valid.log';
        $this->getFilesystem()->dumpFile($filename, json_encode($data));

        $this->context
            ->expects($this->once())
            ->method('getOption')
            ->will($this->returnValue($filename));

        $this->context
            ->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('file'))
            ->will($this->returnValue(true));

        $this->contextRegistry
            ->expects($this->exactly(3))
            ->method('getByStepExecution')
            ->will($this->returnValue($this->context));

        $this->reader->setStepExecution($this->stepExecution);
        $result = $this->reader->read();
        $this->assertEquals($data, $result);
    }

    public function testReadFailed()
    {
        $this->expectException(\Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('Configuration reader must contain "file".');

        $this->context
            ->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('file'))
            ->will($this->returnValue(false));

        $this->contextRegistry
            ->expects($this->once())
            ->method('getByStepExecution')
            ->will($this->returnValue($this->context));

        $this->reader->setStepExecution($this->stepExecution);
        $this->reader->read();
    }

    public function testReadFileNotValid()
    {
        $filename = $this->getTempDir('tracking') . DIRECTORY_SEPARATOR . 'not_valid.log';
        $this->getFilesystem()->touch($filename);

        $this->context
            ->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('file'))
            ->will($this->returnValue(true));

        $this->context
            ->expects($this->once())
            ->method('getOption')
            ->will($this->returnValue($filename));

        $this->contextRegistry
            ->expects($this->exactly(3))
            ->method('getByStepExecution')
            ->will($this->returnValue($this->context));

        $this->reader->setStepExecution($this->stepExecution);
        $this->assertNull($this->reader->read());
        $this->assertNull($this->reader->read());
    }
}
