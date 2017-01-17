<?php
namespace Oro\Bundle\CampaignBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\DashboardBundle\Entity\Dashboard;
use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class LoadCampaignByCloseRevenueWidgetFixture extends AbstractFixture
{
    /**
     * @var Organization
     */
    protected $organization;
    /**
     * @var ObjectManager
     */
    protected $em;

    protected function createLead()
    {
        $lead = new Lead();
        $lead->setName('Lead name');
        $lead->setOrganization($this->organization);
        $lead->setCampaign($this->getReference('default_campaign'));
        $this->em->persist($lead);
        $this->em->flush();
        $this->setReference('default_lead', $lead);
    }

    protected function createOpportunity()
    {
        $className = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE);
        $lostStatus = $this->em->getRepository($className)->find(ExtendHelper::buildEnumValueId('lost'));
        $opportunity = new Opportunity();
        $opportunity->setName('Opportunity name');
        $opportunity->setStatus($lostStatus);
        $opportunity->setLead($this->getReference('default_lead'));
        $opportunity->setOrganization($this->organization);
        $this->em->persist($opportunity);
        $this->em->flush();
    }

    protected function createCampaign()
    {
        $campaign = new Campaign();
        $campaign->setName('Campaign');
        $campaign->setCode('cmp');
        $campaign->setOrganization($this->organization);
        $campaign->setReportPeriod(Campaign::PERIOD_MONTHLY);
        $this->em->persist($campaign);
        $this->em->flush();
        $this->setReference('default_campaign', $campaign);
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $this->organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $this->em = $manager;
        $this->createCampaign();
        $this->createLead();
        $this->createOpportunity();
        $dashboard = new Dashboard();
        $dashboard->setName('dashboard');
        $campaignLeadsWidget = new Widget();
        $campaignLeadsWidget
            ->setDashboard($dashboard)
            ->setName('campaigns_leads')
            ->setLayoutPosition([1, 1]);
        $dashboard->addWidget($campaignLeadsWidget);
        if (!$this->hasReference('widget_campaigns_by_close_revenue')) {
            $this->setReference('widget_campaigns_by_close_revenue', $campaignLeadsWidget);
        }
        $manager->persist($dashboard);
        $manager->flush();
    }
}
