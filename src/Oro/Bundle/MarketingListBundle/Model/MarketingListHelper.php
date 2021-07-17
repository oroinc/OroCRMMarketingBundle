<?php

namespace Oro\Bundle\MarketingListBundle\Model;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

class MarketingListHelper
{
    const MARKETING_LIST = 'Oro\Bundle\MarketingListBundle\Entity\MarketingList';

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $gridName
     * @return int|null
     */
    public function getMarketingListIdByGridName($gridName)
    {
        if (strpos($gridName, ConfigurationProvider::GRID_PREFIX) === false) {
            return null;
        }

        $id = (int)str_replace(ConfigurationProvider::GRID_PREFIX, '', $gridName);
        if (empty($id)) {
            return null;
        }

        return $id;
    }

    /**
     * @param int $id
     * @return MarketingList
     */
    public function getMarketingList($id)
    {
        return $this->managerRegistry
            ->getRepository(self::MARKETING_LIST)
            ->find($id);
    }
}
