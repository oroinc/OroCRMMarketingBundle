<?php

namespace Oro\Bundle\MarketingActivityBundle\Migrations\Data\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;

class LoadDataFieldEnumValues extends AbstractEnumFixture
{
    /** @var array */
    protected $enumData = [
        'ma_type' => [
            MarketingActivity::TYPE_SEND        => 'Send',
            MarketingActivity::TYPE_OPEN        => 'Open',
            MarketingActivity::TYPE_CLICK       => 'Click',
            MarketingActivity::TYPE_SOFT_BOUNCE => 'Soft Bounce',
            MarketingActivity::TYPE_HARD_BOUNCE => 'Hard Bounce',
            MarketingActivity::TYPE_UNSUBSCRIBE => 'Unsubscribe',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityManagerInterface $manager */
        $this->loadEnumValues($this->enumData, $manager);
    }
}
