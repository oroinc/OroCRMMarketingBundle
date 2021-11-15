<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\ImportExport;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\TrackingBundle\ImportExport\LogReader;
use Oro\Component\Testing\TempDirExtension;
use Symfony\Component\Filesystem\Filesystem;

class LogReaderTest extends \PHPUnit\Framework\TestCase
{
    use TempDirExtension;

    /** @var ContextRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $contextRegistry;

    /** @var ContextInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $context;

    /** @var StepExecution|\PHPUnit\Framework\MockObject\MockObject */
    private $stepExecution;

    /** @var LogReader */
    private $reader;

    protected function setUp(): void
    {
        $this->contextRegistry = $this->createMock(ContextRegistry::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);

        $this->reader = new LogReader($this->contextRegistry);
    }

    private function getFilesystem(): Filesystem
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
        $this->getFilesystem()->dumpFile($filename, json_encode($data, JSON_THROW_ON_ERROR));

        $this->context->expects($this->once())
            ->method('getOption')
            ->willReturn($filename);

        $this->context->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('file'))
            ->willReturn(true);

        $this->contextRegistry->expects($this->exactly(3))
            ->method('getByStepExecution')
            ->willReturn($this->context);

        $this->reader->setStepExecution($this->stepExecution);
        $result = $this->reader->read();
        $this->assertEquals($data, $result);
    }

    public function testReadFailed()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Configuration reader must contain "file".');

        $this->context->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('file'))
            ->willReturn(false);

        $this->contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->willReturn($this->context);

        $this->reader->setStepExecution($this->stepExecution);
        $this->reader->read();
    }

    public function testReadFileNotValid()
    {
        $filename = $this->getTempDir('tracking') . DIRECTORY_SEPARATOR . 'not_valid.log';
        $this->getFilesystem()->touch($filename);

        $this->context->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('file'))
            ->willReturn(true);

        $this->context->expects($this->once())
            ->method('getOption')
            ->willReturn($filename);

        $this->contextRegistry->expects($this->exactly(3))
            ->method('getByStepExecution')
            ->willReturn($this->context);

        $this->reader->setStepExecution($this->stepExecution);
        $this->assertNull($this->reader->read());
        $this->assertNull($this->reader->read());
    }
}
