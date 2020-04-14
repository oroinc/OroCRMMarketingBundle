<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Provider;

use Oro\Bundle\CampaignBundle\Provider\EmailTransportProvider;

class EmailTransportProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testProviderMethods()
    {
        $provider = new EmailTransportProvider();
        $name = 'test';
        $transport = $this->createMock('Oro\Bundle\CampaignBundle\Transport\TransportInterface');
        $transport->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($name));

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
        $transportOne = $this->createMock('Oro\Bundle\CampaignBundle\Transport\TransportInterface');
        $transportOne->expects($this->exactly(2))
            ->method('getName')
            ->will($this->returnValue('t1'));
        $transportOne->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue('Transport 1'));
        $transportTwo = $this->createMock('Oro\Bundle\CampaignBundle\Transport\TransportInterface');
        $transportTwo->expects($this->exactly(2))
            ->method('getName')
            ->will($this->returnValue('t2'));
        $transportTwo->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue('Transport 2'));
        $transportTree = $this->createMock('Oro\Bundle\CampaignBundle\Tests\Unit\Provider\TransportStub');
        $transportTree->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('t3'));
        $transportTree->expects($this->never())
            ->method('getLabel')
            ->will($this->returnValue('Transport 3'));
        $transportTree->expects($this->once())
            ->method('isVisibleInForm')
            ->will($this->returnValue(false));

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
