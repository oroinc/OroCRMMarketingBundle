<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Behat\Context;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderAwareInterface;
use Oro\Bundle\TestFrameworkBundle\Behat\Fixtures\FixtureLoaderDictionary;

/**
 * This context save behat execution time, all detailed steps can be found at
 * - "Manage MarketingList Feature"
 */
class MarketingListContext extends OroFeatureContext implements FixtureLoaderAwareInterface
{
    use FixtureLoaderDictionary;

    /**
     * @When /^I load Marketing List fixture$/
     */
    public function enableMarketingListFeature()
    {
        /** @var MarketingListType[]|array $types */
        $types = $this->getEntityManager()->getRepository(MarketingListType::class)->findAll();

        foreach ($types as $type) {
            $this->fixtureLoader->addReference(sprintf('marketing_list_%s_type', strtolower($type->getName())), $type);
        }

        $this->fixtureLoader->loadFixtureFile('OroMarketingListBundle:MarketingListFixture.yml');
    }

    /**
     * @return ObjectManager|object
     */
    protected function getEntityManager()
    {
        return $this->getAppContainer()->get('doctrine')->getManager();
    }
}
