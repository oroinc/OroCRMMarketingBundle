<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Tools;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Tools\AssociationBuilder;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;
use Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentificationProvider;
use Oro\Bundle\TrackingBundle\Tools\IdentifierVisitConfigDumperExtension;

class IdentifierVisitConfigDumperExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var TrackingEventIdentificationProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $identifyProvider;

    /** @var AssociationBuilder|\PHPUnit\Framework\MockObject\MockObject */
    private $associationBuilder;

    /** @var IdentifierVisitConfigDumperExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->identifyProvider = $this->createMock(TrackingEventIdentificationProvider::class);
        $this->associationBuilder = $this->createMock(AssociationBuilder::class);

        $this->extension = new IdentifierVisitConfigDumperExtension(
            $this->identifyProvider,
            $this->configManager,
            $this->associationBuilder
        );
    }

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports(string $className, array $configs, bool $expected)
    {
        $this->prepareMocks($className, $configs);

        $this->assertEquals(
            $expected,
            $this->extension->supports(ExtendConfigDumper::ACTION_PRE_UPDATE)
        );

        $this->assertFalse(
            $this->extension->supports(ExtendConfigDumper::ACTION_POST_UPDATE)
        );
    }

    public function supportsProvider(): array
    {
        return [
            [
                'Test\Entity1',
                [
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'state' => ExtendScope::STATE_NEW,
                    'upgradeable' => false
                ],
                false
            ],
            [
                'Test\Entity1',
                [],
                false
            ],
            [
                'Test\Entity1',
                [
                    'owner' => ExtendScope::OWNER_CUSTOM,
                    'state' => ExtendScope::STATE_NEW,
                    'upgradeable' => true
                ],
                true
            ]
        ];
    }

    /**
     * @depends testSupports
     */
    public function testPreUpdate()
    {
        $data = $this->supportsProvider();
        $data = end($data);

        $this->prepareMocks($data[0], $data[1]);

        $this->associationBuilder->expects($this->once())
            ->method('createManyToOneAssociation')
            ->with(
                TrackingVisit::class,
                $data[0],
                IdentifierEventExtension::ASSOCIATION_KIND
            );

        $this->extension->preUpdate();
    }

    private function prepareMocks(string $className, array $configs): void
    {
        $this->identifyProvider->expects($this->once())
            ->method('getTargetIdentityEntities')
            ->willReturn([$className]);

        $extendConfig = new Config(new EntityConfigId('extend', $className));
        if (!empty($configs)) {
            foreach ($configs as $name => $value) {
                $extendConfig->set($name, $value);
            }
        }

        $extendProvider = $this->createMock(ConfigProvider::class);
        $extendProvider->expects($this->once())
            ->method('getConfigs')
            ->willReturn([$extendConfig]);
        $extendProvider->expects($this->any())
            ->method('hasConfig')
            ->with(TrackingVisit::class)
            ->willReturn(true);

        $this->configManager->expects($this->any())
            ->method('getProvider')
            ->with('extend')
            ->willReturn($extendProvider);
    }
}
