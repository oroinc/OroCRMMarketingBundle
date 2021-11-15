<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationExclusionProvider;

class ContactInformationExclusionProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var VirtualFieldProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $virtualFieldProvider;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var ClassMetadata|\PHPUnit\Framework\MockObject\MockObject */
    private $metadata;

    /** @var ContactInformationExclusionProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->virtualFieldProvider = $this->createMock(VirtualFieldProviderInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->metadata = $this->createMock(ClassMetadata::class);

        $this->provider = new ContactInformationExclusionProvider(
            $this->virtualFieldProvider,
            $this->configProvider,
            $this->registry
        );
    }

    public function testIsIgnoredEntityHasContactInformationField()
    {
        $className = 'stdClass';

        $this->virtualFieldProvider->expects($this->once())
            ->method('isVirtualField')
            ->with($className, 'contactInformation')
            ->willReturn(true);

        $this->assertFalse($this->provider->isIgnoredEntity($className));
    }

    public function testIsIgnoredEntityHasNoContactInformationFields()
    {
        $this->configureRegistry($className = 'stdClass');

        $this->virtualFieldProvider->expects($this->once())
            ->method('isVirtualField')
            ->with($className, 'contactInformation')
            ->willReturn(false);

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

    public function testHasFieldConfigContactInformation()
    {
        $this->configureRegistry($className = 'stdClass', true);

        $this->assertFalse($this->provider->isIgnoredEntity($className));
    }

    public function testAddressesAreIgnored()
    {
        $this->assertTrue($this->provider->isIgnoredEntity(AbstractAddress::class));
    }

    private function configureRegistry($className, $withContactInformation = false)
    {
        $om = $this->createMock(ObjectManager::class);

        $om->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($className))
            ->willReturn($this->metadata);

        $fieldNames = [];

        if ($withContactInformation) {
            $fieldConfig = $this->createMock(ConfigInterface::class);
            $fieldConfig->expects($this->once())
                ->method('has')
                ->with($this->equalTo('contact_information'))
                ->willReturn(true);

            $this->configProvider->expects($this->once())
                ->method('hasConfig')
                ->willReturn(true);

            $this->configProvider->expects($this->once())
                ->method('getConfig')
                ->willReturn($fieldConfig);

            $fieldNames = ['email'];
        }

        $this->metadata->expects($this->once())
            ->method('getFieldNames')
            ->willReturn($fieldNames);

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with($this->equalTo($className))
            ->willReturn($om);
    }
}
