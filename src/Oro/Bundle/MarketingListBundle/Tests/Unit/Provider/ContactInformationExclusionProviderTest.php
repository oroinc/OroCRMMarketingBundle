<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationExclusionProvider;

class ContactInformationExclusionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactInformationExclusionProvider
     */
    protected $provider;

    /**
     * @var VirtualFieldProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $virtualFieldProvider;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    protected function setUp()
    {
        $this->virtualFieldProvider = $this
            ->createMock('Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface');
        $this->metadata = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new ContactInformationExclusionProvider($this->virtualFieldProvider);
    }

    public function testIsIgnoredEntityHasContactInformationField()
    {
        $className = 'stdClass';

        $this->virtualFieldProvider->expects($this->once())
            ->method('isVirtualField')
            ->with($className, 'contactInformation')
            ->will($this->returnValue(true));

        $this->assertFalse($this->provider->isIgnoredEntity($className));
    }

    public function testIsIgnoredEntityHasNoContactInformationFields()
    {
        $className = 'stdClass';

        $this->virtualFieldProvider->expects($this->once())
            ->method('isVirtualField')
            ->with($className, 'contactInformation')
            ->will($this->returnValue(false));

        $this->assertTrue($this->provider->isIgnoredEntity($className));
    }

    public function testIsIgnoredField()
    {
        $this->assertFalse($this->provider->isIgnoredField($this->metadata, 'fieldName'));
    }

    public function testIsIgnoredRelation()
    {
        $this->assertFalse($this->provider->isIgnoredRelation($this->metadata, 'associationName'));
    }
}
