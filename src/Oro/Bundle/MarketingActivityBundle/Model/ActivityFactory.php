<?php

namespace Oro\Bundle\MarketingActivityBundle\Model;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * MarketingActivity entity factory
 */
class ActivityFactory
{
    /**
     * @var EnumOptionsProvider
     */
    protected $enumProvider;

    public function __construct(EnumOptionsProvider $enumProvider)
    {
        $this->enumProvider = $enumProvider;
    }

    /**
     * @param Campaign $campaign
     * @param string $entityClass
     * @param int $entityId
     * @param \DateTime $actionDate
     * @param string $type
     * @param Organization $owner
     * @param int $relatedCampaignId
     * @param string $relatedCampaignClass
     * @return MarketingActivity
     */
    public function create(
        Campaign $campaign,
        $entityClass,
        $entityId,
        \DateTime $actionDate,
        $type,
        Organization $owner,
        $relatedCampaignId,
        $relatedCampaignClass = EmailCampaign::class
    ) {
        $marketingActivity = new MarketingActivity();
        $marketingActivity->setCampaign($campaign);
        $marketingActivity->setEntityClass($entityClass);
        $marketingActivity->setEntityId($entityId);
        $marketingActivity->setType($this->getActivityType($type));
        $marketingActivity->setRelatedCampaignId($relatedCampaignId);
        $marketingActivity->setRelatedCampaignClass($relatedCampaignClass);
        $marketingActivity->setActionDate($actionDate);
        $marketingActivity->setOwner($owner);

        return $marketingActivity;
    }

    /**
     * @param $id
     * @return EnumOptionInterface
     */
    protected function getActivityType($id)
    {
        return $this->enumProvider->getEnumOptionByCode(MarketingActivity::TYPE_ENUM_CODE, $id);
    }
}
