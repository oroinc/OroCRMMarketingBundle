<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class LoadCampaignSalesData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadCampaignData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Campaign $campaign1 */
        $campaign1 = $this->getReference('Campaign.Campaign1');
        $this->loadOpportunitiesWithLeads($campaign1, $manager, 'Campaign.Campaign1');
        /** @var Campaign $campaign2 */
        $campaign2 = $this->getReference('Campaign.Campaign2');
        $this->loadOpportunitiesWithLeads($campaign2, $manager, 'Campaign.Campaign2');
    }

    private function loadOpportunitiesWithLeads(Campaign $campaign, ObjectManager $manager, string $prefix): void
    {
        $createdToday = new \DateTime('now', new \DateTimeZone('UTC'));
        $createdPastYear = new \DateTime('-1 year', new \DateTimeZone('UTC'));

        $lead = $this->createLead($campaign, $manager, $prefix.'.lead.now', $createdToday);
        $this->createOpportunity($manager, $prefix.'.opportunity.now', $lead);

        $lead = $this->createLead($campaign, $manager, $prefix.'.lead.past_year', $createdPastYear);
        $this->createOpportunity($manager, $prefix.'.opportunity.past_year', $lead);

        $manager->flush();
    }

    private function createLead(Campaign $campaign, ObjectManager $manager, string $name, \DateTime $createdAt): Lead
    {
        $lead = new Lead();
        $lead->setName($name);
        $lead->setOrganization($campaign->getOrganization());
        $lead->setOwner($campaign->getOwner());
        $lead->setCampaign($campaign);
        $manager->persist($lead);

        $lead->setCreatedAt($createdAt);
        $this->setReference($name, $lead);

        return $lead;
    }

    private function createOpportunity(
        ObjectManager $manager,
        string $name,
        Lead $lead
    ): void {
        $opportunity = new Opportunity();
        $opportunity->setName($name);
        $opportunity->setOrganization($lead->getOrganization());
        $opportunity->setOwner($lead->getOwner());
        $opportunity->setLead($lead);

        $opportunity->setStatus($this->getStatus('won', $manager));
        $opportunity->setCloseRevenueCurrency('USD');
        $opportunity->setCloseRevenueValue(100.0);

        $manager->persist($opportunity);

        $opportunity->setCreatedAt($lead->getCreatedAt());
        $this->setReference($name, $opportunity);
    }

    /**
     * @param string $statusId
     * @param ObjectManager $manager
     * @return AbstractEnumValue
     */
    private function getStatus($statusId, ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);

        return $manager->getRepository($className)->find(ExtendHelper::buildEnumValueId($statusId));
    }
}
