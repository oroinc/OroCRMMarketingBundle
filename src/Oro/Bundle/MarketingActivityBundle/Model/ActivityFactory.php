<?php

namespace Oro\Bundle\MarketingActivityBundle\Model;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class ActivityFactory
{
    /**
     * @var EnumValueProvider
     */
    protected $enumProvider;

    public function __construct(EnumValueProvider $enumProvider)
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
     * @return AbstractEnumValue
     */
    protected function getActivityType($id)
    {
        return $this->enumProvider->getEnumValueByCode(MarketingActivity::TYPE_ENUM_CODE, $id);
    }
}
