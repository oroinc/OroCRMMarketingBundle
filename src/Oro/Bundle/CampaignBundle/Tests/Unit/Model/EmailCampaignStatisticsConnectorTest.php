<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Model;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignStatisticsConnector;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListItem;
use Oro\Bundle\MarketingListBundle\Model\MarketingListItemConnector;

class EmailCampaignStatisticsConnectorTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListItemConnector|\PHPUnit\Framework\MockObject\MockObject */
    private $marketingListItemConnector;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var EmailCampaignStatisticsConnector */
    private $connector;

    protected function setUp(): void
    {
        $this->marketingListItemConnector = $this->createMock(MarketingListItemConnector::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->connector = new EmailCampaignStatisticsConnector(
            $this->marketingListItemConnector,
            $this->doctrineHelper
        );
    }

    /**
     * @dataProvider existingDataProvider
     */
    public function testGetStatisticsRecordExisting(bool $existing)
    {
        $entity = new \stdClass();
        $entityId = 1;
        $entityClass = get_class($entity);

        $marketingList = $this->createMock(MarketingList::class);

        $emailCampaign = $this->createMock(EmailCampaign::class);
        $emailCampaign->expects($this->exactly(2))
            ->method('getMarketingList')
            ->willReturn($marketingList);

        $this->doctrineHelper->expects($this->exactly(2))
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->willReturn($entityId);

        $marketingListItem = $this->createMock(MarketingListItem::class);
        $marketingListItem->expects($this->any())
            ->method('getId')
            ->willReturn(42);

        /**
         * Check marketingListItem cache
         */
        $this->marketingListItemConnector->expects($this->once())
            ->method('getMarketingListItem')
            ->with($marketingList, $entityId)
            ->willReturn($marketingListItem);

        $statisticsRecord = new EmailCampaignStatistics();

        $repository = $this->createMock(ObjectRepository::class);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->exactly(2))
            ->method('getRepository')
            ->with($entityClass)
            ->willReturn($repository);
        $this->doctrineHelper->expects($this->exactly(2))
            ->method('getEntityManager')
            ->with($entityClass)
            ->willReturn($manager);

        if ($existing) {
            $repository->expects($this->exactly(2))
                ->method('findOneBy')
                ->with(['emailCampaign' => $emailCampaign, 'marketingListItem' => $marketingListItem])
                ->willReturn($statisticsRecord);
        } else {
            $repository->expects($this->exactly(2))
                ->method('findOneBy')
                ->with(['emailCampaign' => $emailCampaign, 'marketingListItem' => $marketingListItem])
                ->willReturn(null);

            $manager->expects($this->once())
                ->method('persist')
                ->with($this->isInstanceOf(EmailCampaignStatistics::class));
        }

        $this->connector->setEntityName($entityClass);
        $actualRecord = $this->connector->getStatisticsRecord($emailCampaign, $entity);
        /**
         * Check marketingListItem cache
         */
        $this->connector->getStatisticsRecord($emailCampaign, $entity);

        if (!$existing) {
            $this->assertEquals($emailCampaign, $actualRecord->getEmailCampaign(), 'unexpected email campaign');
            $this->assertEquals(
                $marketingListItem,
                $actualRecord->getMarketingListItem(),
                'unexpected marketing list item campaign'
            );
        } else {
            $this->assertEquals($statisticsRecord, $actualRecord);
        }
    }

    public function existingDataProvider(): array
    {
        return [
            'existing' => [true],
            'not existing' => [false]
        ];
    }
}
