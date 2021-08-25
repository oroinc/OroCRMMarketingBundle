<?php

namespace Oro\Bundle\MarketingListBundle\Acl\Voter;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\SecurityBundle\Acl\BasicPermission;
use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;

/**
 * Prevents editing and removal of segments that are used by marketing lists.
 */
class MarketingListSegmentVoter extends AbstractEntityVoter
{
    /** {@inheritDoc} */
    protected $supportedAttributes = [BasicPermission::EDIT, BasicPermission::DELETE];

    private array $marketingListBySegment = [];

    /**
     * {@inheritDoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if ($this->getMarketingListBySegment($identifier)) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }

    private function getMarketingListBySegment(int $segmentId): ?MarketingList
    {
        if (empty($this->marketingListBySegment[$segmentId])) {
            $segment = $this->doctrineHelper->getEntityReference($this->className, $segmentId);
            $marketingList = $this->doctrineHelper
                ->getEntityRepository(MarketingList::class)
                ->findOneBy(['segment' => $segment]);
            $this->marketingListBySegment[$segmentId] = $marketingList;
        }

        return $this->marketingListBySegment[$segmentId];
    }
}
