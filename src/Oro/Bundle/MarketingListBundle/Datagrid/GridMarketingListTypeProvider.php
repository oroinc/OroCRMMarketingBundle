<?php

namespace Oro\Bundle\MarketingListBundle\Datagrid;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;

class GridMarketingListTypeProvider
{
    const MARKETING_LIST_TYPE = 'OroMarketingListBundle:MarketingListType';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Get marketing list types choices.
     *
     * @return array
     */
    public function getListTypeChoices()
    {
        /** @var MarketingListType[] $types */
        $types = $this->registry
            ->getManagerForClass(self::MARKETING_LIST_TYPE)
            ->getRepository(self::MARKETING_LIST_TYPE)
            ->findBy([], ['name' => 'ASC']);

        $results = [];
        foreach ($types as $type) {
            $results[$type->getLabel()] = $type->getName();
        }

        return $results;
    }
}
