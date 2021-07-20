<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Functional\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\TestFrameworkBundle\Entity\TestActivity;

class LoadTestEntityData extends AbstractFixture
{
    const TEST_ENTITY_1 = 'test_entity_1';
    const TEST_ENTITY_2 = 'test_entity_2';
    const TEST_ENTITY_3 = 'test_entity_3';

    /**
     * @var array
     */
    protected $entities = [
        self::TEST_ENTITY_1 => 1,
        self::TEST_ENTITY_2 => 2,
        self::TEST_ENTITY_3 => 3,
    ];

    public function load(ObjectManager $manager)
    {
        foreach ($this->entities as $name => $entityId) {
            $entity = new TestActivity();
            $entity->setId($entityId)
                ->setMessage('test message');

            $manager->persist($entity);
            $this->addReference($name, $entity);
        }

        $manager->flush();
    }
}
