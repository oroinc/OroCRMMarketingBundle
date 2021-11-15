<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Provider;

use Oro\Bundle\CampaignBundle\Provider\EmailTransportProvider;
use Oro\Bundle\CampaignBundle\Transport\TransportInterface;

class EmailTransportProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testProviderMethods()
    {
        $provider = new EmailTransportProvider();
        $name = 'test';
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        $this->assertEmpty($provider->getTransports());
        $this->assertFalse($provider->hasTransport($name));

        $provider->addTransport($transport);
        $this->assertTrue($provider->hasTransport($name));
        $this->assertCount(1, $provider->getTransports());
        $this->assertEquals($transport, $provider->getTransportByName($name));
    }

    public function testTransportActualChoices()
    {
        $choices = ['Transport 1' => 't1', 'Transport 2' => 't2'];
        $provider = new EmailTransportProvider();
        $transportOne = $this->createMock(TransportInterface::class);
        $transportOne->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('t1');
        $transportOne->expects($this->once())
            ->method('getLabel')
            ->willReturn('Transport 1');
        $transportTwo = $this->createMock(TransportInterface::class);
        $transportTwo->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('t2');
        $transportTwo->expects($this->once())
            ->method('getLabel')
            ->willReturn('Transport 2');
        $transportTree = $this->createMock(TransportStub::class);
        $transportTree->expects($this->once())
            ->method('getName')
            ->willReturn('t3');
        $transportTree->expects($this->never())
            ->method('getLabel')
            ->willReturn('Transport 3');
        $transportTree->expects($this->once())
            ->method('isVisibleInForm')
            ->willReturn(false);

        $provider->addTransport($transportOne);
        $provider->addTransport($transportTwo);
        $provider->addTransport($transportTree);
        $this->assertEquals($choices, $provider->getVisibleTransportChoices());
    }

    public function testGetTransportException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transport test is unknown');

        $provider = new EmailTransportProvider();
        $provider->getTransportByName('test');
    }
}
