<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Model;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use Oro\Bundle\CampaignBundle\Entity\TransportSettings;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSender;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignStatisticsConnector;
use Oro\Bundle\CampaignBundle\Provider\EmailTransportProvider;
use Oro\Bundle\CampaignBundle\Transport\TransportInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListItem;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListProvider;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmailCampaignSenderTest extends \PHPUnit\Framework\TestCase
{
    const ENTITY_ID = 1;

    /**
     * @var EmailCampaignSender
     */
    protected $sender;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ConfigManager
     */
    protected $configManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EmailCampaignStatisticsConnector
     */
    protected $statisticsConnector;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    protected $registry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    protected $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TransportInterface
     */
    protected $transport;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EmailTransportProvider
     */
    protected $transportProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ValidatorInterface
     */
    protected $validator;

    protected function setUp(): void
    {
        $this->marketingListProvider = $this->createMock(MarketingListProvider::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->statisticsConnector = $this->createMock(EmailCampaignStatisticsConnector::class);
        $this->contactInformationFieldsProvider = $this->createMock(ContactInformationFieldsProvider::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->transport = $this->createMock(TransportInterface::class);
        $this->transportProvider = $this->createMock(EmailTransportProvider::class);

        $this->sender = new EmailCampaignSender(
            $this->marketingListProvider,
            $this->configManager,
            $this->statisticsConnector,
            $this->contactInformationFieldsProvider,
            $this->registry,
            $this->transportProvider
        );

        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->sender->setValidator($this->validator);
        $this->sender->setLogger($this->logger);
    }

    protected function tearDown(): void
    {
        unset($this->sender);
    }

    public function testAssertTransport()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transport is required to perform send');

        $campaign = new EmailCampaign();

        $this->sender->send();
    }

    public function testAssertTransportInvalidSettings()
    {
        $this->transportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->will($this->returnValue($this->transport));

        $transportSettings = $this->createMock(TransportSettings::class);

        $campaign = new EmailCampaign();
        $campaign->setTransportSettings($transportSettings);

        $constraint = $this->createMock(ConstraintViolationList::class);
        $constraint->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($transportSettings)
            ->will($this->returnValue($constraint));

        $this->sender->setEmailCampaign($campaign);

        $this->sender->send();
    }

    /**
     * @param array $iterable
     * @param array $to
     * @param object $type
     * @dataProvider sendDataProvider
     */
    public function testSend($iterable, $to, $type)
    {
        $segment = new Segment();
        $entity = '\stdClass';

        $marketingList = new MarketingList();
        $marketingList->setSegment($segment);
        $marketingList->setType($type);
        $marketingList->setEntity($entity);

        $transportSettings = $this->createMock(TransportSettings::class);
        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setSenderName(reset($to))
            ->setSenderEmail(reset($to))
            ->setTransportSettings($transportSettings);

        $constraint = $this->createMock(ConstraintViolationList::class);
        $constraint->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($transportSettings)
            ->will($this->returnValue($constraint));

        $itCount = count($iterable);
        $this->marketingListProvider
            ->expects($this->once())
            ->method('getEntitiesIterator')
            ->will($this->returnValue($iterable));

        $manager = $this->createMock(EntityManager::class);
        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));
        $manager->expects($this->exactly($itCount))
            ->method('flush');
        $manager->expects($this->exactly($itCount))
            ->method('beginTransaction');
        $manager->expects($this->exactly($itCount))
            ->method('commit');

        $fields = ['email'];
        $this->assertFieldsCall($fields, $marketingList);
        if ($itCount) {
            $this->contactInformationFieldsProvider
                ->expects($this->exactly($itCount * 2))
                ->method('getTypedFieldsValues')
                ->with(
                    $this->equalTo($fields),
                    $this->anything()
                )
                ->will($this->returnValue($to));

            $marketingListItem = $this->createMock(MarketingListItem::class);
            $marketingListItem->expects($this->exactly($itCount))
                ->method('contact');

            $statisticsRecord = $this->createMock(EmailCampaignStatistics::class);
            $statisticsRecord->expects($this->exactly($itCount))
                ->method('getMarketingListItem')
                ->will($this->returnValue($marketingListItem));
            $this->statisticsConnector
                ->expects($this->exactly($itCount))
                ->method('getStatisticsRecord')
                ->with(
                    $this->equalTo($campaign),
                    $this->isInstanceOf('stdClass')
                )
                ->will($this->returnValue($statisticsRecord));
        }

        $this->transport->expects($this->exactly($itCount))
            ->method('send');

        $this->transportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->will($this->returnValue($this->transport));

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send();
    }

    /**
     * @dataProvider sendWithoutEmailContactInformationDataProvider
     */
    public function testSendWithoutEmailContactInformation(array $marketingListItems, string $marketingListTypeName)
    {
        $marketingListType = new MarketingListType($marketingListTypeName);

        $segment = new Segment();
        $entity = '\stdClass';

        $marketingList = new MarketingList();
        $marketingList->setSegment($segment);
        $marketingList->setType($marketingListType);
        $marketingList->setEntity($entity);

        $transportSettings = $this->createMock(TransportSettings::class);
        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setTransportSettings($transportSettings);

        $constraint = $this->createMock(ConstraintViolationList::class);
        $constraint->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($transportSettings)
            ->will($this->returnValue($constraint));

        $this->marketingListProvider
            ->expects($this->once())
            ->method('getEntitiesIterator')
            ->will($this->returnValue($marketingListItems));

        $manager = $this->createMock(EntityManager::class);
        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));
        $manager->expects($this->never())
            ->method('flush');
        $manager->expects($this->never())
            ->method('beginTransaction');
        $manager->expects($this->never())
            ->method('commit');

        $fields = ['email'];
        $this->assertFieldsCall($fields, $marketingList);
        $this->contactInformationFieldsProvider
            ->expects($this->any())
            ->method('getTypedFieldsValues')
            ->will($this->returnValue([]));

        $this->statisticsConnector
            ->expects($this->never())
            ->method('getStatisticsRecord');

        $this->transport->expects($this->never())
            ->method('send');

        $this->transportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->will($this->returnValue($this->transport));

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send();
    }

    /**
     * @return array
     */
    public function sendWithoutEmailContactInformationDataProvider()
    {
        $entity = $this->getMockBuilder('\stdClass')
            ->setMethods(['getId'])
            ->getMock();

        return [
            [
                [[$entity], [$entity]],
                MarketingListType::TYPE_MANUAL
            ],
            [
                [[$entity], [$entity]],
                MarketingListType::TYPE_DYNAMIC
            ],
        ];
    }

    public function testSendWithCertainFields()
    {
        $segment = new Segment();
        $marketingList = new MarketingList();
        $marketingList->setSegment($segment);
        $marketingList->setType(new MarketingListType(MarketingListType::TYPE_DYNAMIC));
        $marketingList->setEntity(\stdClass::class);

        /** @var TransportSettings $transportSettings */
        $transportSettings = $this->createMock(TransportSettings::class);
        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setSenderName('Sender')
            ->setSenderEmail('sender@example.com')
            ->setTransportSettings($transportSettings);

        $constraint = $this->createMock(ConstraintViolationList::class);
        $constraint->expects($this->once())
            ->method('count')
            ->willReturnArgument(0);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($transportSettings)
            ->willReturn($constraint);

        $toFromFields = 'to@example.com';
        $toFromEntity = 'toFromEntity@example.com';
        $entity = new \stdClass();
        $this->marketingListProvider
            ->expects($this->once())
            ->method('getEntitiesIterator')
            ->willReturn([
                [
                    $entity,
                    'firstField' => $toFromFields,
                    'secondField' => 'secondValue',
                ]
            ]);

        $manager = $this->createMock(EntityManager::class);
        $this->registry->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);
        $manager->expects($this->once())
            ->method('flush');

        $fields = ['firstField'];
        $this->assertFieldsCall($fields, $marketingList);
        $this->contactInformationFieldsProvider
            ->expects($this->exactly(2))
            ->method('getTypedFieldsValues')
            ->withConsecutive(
                [
                    $fields,
                    [
                        'firstField' => $toFromFields,
                        'secondField' => 'secondValue',
                    ]
                ],
                [
                    $fields,
                    $entity
                ]
            )
            ->willReturnOnConsecutiveCalls([$toFromFields], [$toFromEntity]);

        $marketingListItem = $this->createMock(MarketingListItem::class);
        $marketingListItem->expects($this->once())
            ->method('contact');
        $statisticsRecord = $this->createMock(EmailCampaignStatistics::class);
        $statisticsRecord->expects($this->once())
            ->method('getMarketingListItem')
            ->willReturn($marketingListItem);
        $this->statisticsConnector
            ->expects($this->once())
            ->method('getStatisticsRecord')
            ->with($campaign, $entity)
            ->willReturn($statisticsRecord);

        $this->transport->expects($this->once())
            ->method('send')
            ->with(
                $campaign,
                $entity,
                ['sender@example.com' => 'Sender'],
                [$toFromFields, $toFromEntity]
            );

        $this->transportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->willReturn($this->transport);

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send();
    }

    /**
     * @param array $iterable
     * @param array $to
     * @param object $type
     * @dataProvider sendDataProvider
     */
    public function testSendError($iterable, $to, $type)
    {
        $segment = new Segment();
        $entity = '\stdClass';

        $marketingList = new MarketingList();
        $marketingList->setSegment($segment);
        $marketingList->setType($type);
        $marketingList->setEntity($entity);

        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setSenderEmail(reset($to));

        $itCount = count($iterable);
        $this->marketingListProvider
            ->expects($this->once())
            ->method('getEntitiesIterator')
            ->will($this->returnValue($iterable));

        $manager = $this->createMock(EntityManager::class);
        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));
        $manager->expects($this->never())
            ->method('flush');
        $manager->expects($this->exactly($itCount))
            ->method('beginTransaction');
        $manager->expects($this->exactly($itCount))
            ->method('rollback');

        $fields = ['email'];
        $this->assertFieldsCall($fields, $marketingList);
        if ($itCount) {
            $this->contactInformationFieldsProvider
                ->expects($this->exactly($itCount * 2))
                ->method('getTypedFieldsValues')
                ->with(
                    $this->equalTo($fields),
                    $this->anything()
                )
                ->will($this->returnValue($to));

            $this->statisticsConnector
                ->expects($this->exactly($itCount))
                ->method('getStatisticsRecord')
                ->with(
                    $this->equalTo($campaign),
                    $this->isInstanceOf('stdClass')
                )
                ->willThrowException(new \Exception('Error'));

            $this->logger->expects($this->exactly($itCount))
                ->method('error');
        }

        $this->transport->expects($this->exactly($itCount))
            ->method('send');

        $this->transportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->will($this->returnValue($this->transport));

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send();
    }

    protected function assertFieldsCall($fields, MarketingList $marketingList)
    {
        $this->contactInformationFieldsProvider->expects($this->once())
            ->method('getMarketingListTypedFields')
            ->with($marketingList, ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL)
            ->will($this->returnValue($fields));
    }

    /**
     * @return array
     */
    public function sendDataProvider()
    {
        $entity = $this->getMockBuilder('\stdClass')
            ->setMethods(['getId'])
            ->getMock();

        $manualType = new MarketingListType(MarketingListType::TYPE_MANUAL);
        $segmentBasedType = new MarketingListType(MarketingListType::TYPE_DYNAMIC);

        return [
            [[], ['mail@example.com'], $manualType],
            [[[$entity]], ['mail@example.com'], $manualType],
            [[[$entity], [$entity]], ['mail@example.com'], $manualType],

            [[], ['mail@example.com'], $segmentBasedType],
            [[[$entity]], ['mail@example.com'], $segmentBasedType],
            [[[$entity], [$entity]], ['mail@example.com'], $segmentBasedType],
        ];
    }
}
