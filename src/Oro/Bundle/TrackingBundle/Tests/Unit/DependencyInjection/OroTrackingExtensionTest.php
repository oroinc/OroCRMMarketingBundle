<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TrackingBundle\DependencyInjection\OroTrackingExtension;

class OroTrackingExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OroTrackingExtension
     */
    protected $extension;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new OroTrackingExtension();
    }

    public function testLoad()
    {
        $this->container->expects($this->once())
            ->method('prependExtensionConfig')
            ->with('oro_tracking', $this->isType('array'));
        $this->extension->load([], $this->container);
    }
}
