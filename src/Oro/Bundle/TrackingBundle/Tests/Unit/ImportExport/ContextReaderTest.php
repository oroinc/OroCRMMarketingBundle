<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\ImportExport;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\TrackingBundle\ImportExport\ContextReader;

class ContextReaderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContextRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $contextRegistry;

    /** @var ContextInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $context;

    /** @var StepExecution|\PHPUnit\Framework\MockObject\MockObject */
    private $stepExecution;

    /** @var ContextReader */
    private $reader;

    protected function setUp(): void
    {
        $this->contextRegistry = $this->createMock(ContextRegistry::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);

        $this->reader = new ContextReader($this->contextRegistry);
    }

    public function testRead()
    {
        $data = [
            'name'  => 'event_name',
            'value' => 'done'
        ];

        $this->context->expects($this->once())
            ->method('getOption')
            ->willReturn($data);

        $this->context->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('data'))
            ->willReturn(true);

        $this->contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->willReturn($this->context);

        $this->reader->setStepExecution($this->stepExecution);
        $result = $this->reader->read();
        $this->assertEquals($data, $result);
    }

    public function testReadFailed()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Configuration reader must contain "data".');

        $this->context->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('data'))
            ->willReturn(false);

        $this->contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->willReturn($this->context);

        $this->reader->setStepExecution($this->stepExecution);
        $this->reader->read();
    }
}
