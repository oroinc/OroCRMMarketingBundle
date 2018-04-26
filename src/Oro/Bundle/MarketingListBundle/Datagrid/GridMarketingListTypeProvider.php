<?php

namespace Oro\Bundle\MarketingListBundle\Datagrid;

use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Symfony\Bridge\Doctrine\RegistryInterface;

class GridMarketingListTypeProvider
{
    const MARKETING_LIST_TYPE = 'OroMarketingListBundle:MarketingListType';

    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
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
