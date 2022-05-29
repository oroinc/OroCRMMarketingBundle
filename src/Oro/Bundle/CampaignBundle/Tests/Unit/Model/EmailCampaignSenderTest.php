<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
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
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmailCampaignSenderTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $marketingListProvider;

    /** @var EmailCampaignStatisticsConnector|\PHPUnit\Framework\MockObject\MockObject */
    private $statisticsConnector;

    /** @var ContactInformationFieldsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $contactInformationFieldsProvider;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var TransportInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $transport;

    /** @var EmailTransportProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $transportProvider;

    /** @var ValidatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $validator;

    /** @var EmailCampaignSender */
    private $sender;

    protected function setUp(): void
    {
        $this->marketingListProvider = $this->createMock(MarketingListProvider::class);
        $this->statisticsConnector = $this->createMock(EmailCampaignStatisticsConnector::class);
        $this->contactInformationFieldsProvider = $this->createMock(ContactInformationFieldsProvider::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->transport = $this->createMock(TransportInterface::class);
        $this->transportProvider = $this->createMock(EmailTransportProvider::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->sender = new EmailCampaignSender(
            $this->marketingListProvider,
            $this->createMock(ConfigManager::class),
            $this->statisticsConnector,
            $this->contactInformationFieldsProvider,
            $this->doctrine,
            $this->transportProvider
        );
        $this->sender->setValidator($this->validator);
        $this->sender->setLogger($this->logger);
    }

    public function testAssertTransport()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transport is required to perform send');

        $this->sender->send();
    }

    public function testAssertTransportInvalidSettings()
    {
        $this->transportProvider->expects($this->once())
            ->method('getTransportByName')
            ->willReturn($this->transport);

        $transportSettings = $this->createMock(TransportSettings::class);

        $campaign = new EmailCampaign();
        $campaign->setTransportSettings($transportSettings);

        $constraint = $this->createMock(ConstraintViolationList::class);
        $constraint->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($transportSettings)
            ->willReturn($constraint);

        $this->sender->setEmailCampaign($campaign);

        $this->sender->send();
    }

    /**
     * @dataProvider sendDataProvider
     */
    public function testSend(array $iterable, array $to, MarketingListType $type)
    {
        $segment = new Segment();
        $entity = \stdClass::class;

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
            ->willReturn(0);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($transportSettings)
            ->willReturn($constraint);

        $itCount = count($iterable);
        $this->marketingListProvider->expects($this->once())
            ->method('getEntitiesIterator')
            ->willReturn($iterable);

        $manager = $this->createMock(EntityManager::class);
        $this->doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);
        $manager->expects($this->exactly($itCount))
            ->method('flush');
        $manager->expects($this->exactly($itCount))
            ->method('beginTransaction');
        $manager->expects($this->exactly($itCount))
            ->method('commit');

        $fields = ['email'];
        $this->assertFieldsCall($fields, $marketingList);
        if ($itCount) {
            $this->contactInformationFieldsProvider->expects($this->exactly($itCount * 2))
                ->method('getTypedFieldsValues')
                ->with($fields, $this->anything())
                ->willReturn($to);

            $marketingListItem = $this->createMock(MarketingListItem::class);
            $marketingListItem->expects($this->exactly($itCount))
                ->method('contact');

            $statisticsRecord = $this->createMock(EmailCampaignStatistics::class);
            $statisticsRecord->expects($this->exactly($itCount))
                ->method('getMarketingListItem')
                ->willReturn($marketingListItem);
            $this->statisticsConnector->expects($this->exactly($itCount))
                ->method('getStatisticsRecord')
                ->with($this->identicalTo($campaign), $this->isInstanceOf(\stdClass::class))
                ->willReturn($statisticsRecord);
        }

        $this->transport->expects($this->exactly($itCount))
            ->method('send');

        $this->transportProvider->expects($this->once())
            ->method('getTransportByName')
            ->willReturn($this->transport);

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
        $entity = \stdClass::class;

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
            ->willReturn(0);
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($transportSettings)
            ->willReturn($constraint);

        $this->marketingListProvider->expects($this->once())
            ->method('getEntitiesIterator')
            ->willReturn($marketingListItems);

        $manager = $this->createMock(EntityManager::class);
        $this->doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);
        $manager->expects($this->never())
            ->method('flush');
        $manager->expects($this->never())
            ->method('beginTransaction');
        $manager->expects($this->never())
            ->method('commit');

        $fields = ['email'];
        $this->assertFieldsCall($fields, $marketingList);
        $this->contactInformationFieldsProvider->expects($this->any())
            ->method('getTypedFieldsValues')
            ->willReturn([]);

        $this->statisticsConnector->expects($this->never())
            ->method('getStatisticsRecord');

        $this->transport->expects($this->never())
            ->method('send');

        $this->transportProvider->expects($this->once())
            ->method('getTransportByName')
            ->willReturn($this->transport);

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send();
    }

    public function sendWithoutEmailContactInformationDataProvider(): array
    {
        $entity = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getId'])
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
        $this->marketingListProvider->expects($this->once())
            ->method('getEntitiesIterator')
            ->willReturn([
                [
                    $entity,
                    'firstField' => $toFromFields,
                    'secondField' => 'secondValue',
                ]
            ]);

        $manager = $this->createMock(EntityManager::class);
        $this->doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);
        $manager->expects($this->once())
            ->method('flush');

        $fields = ['firstField'];
        $this->assertFieldsCall($fields, $marketingList);
        $this->contactInformationFieldsProvider->expects($this->exactly(2))
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
        $this->statisticsConnector->expects($this->once())
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

        $this->transportProvider->expects($this->once())
            ->method('getTransportByName')
            ->willReturn($this->transport);

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send();
    }

    /**
     * @dataProvider sendDataProvider
     */
    public function testSendError(array $iterable, array $to, MarketingListType $type)
    {
        $segment = new Segment();
        $entity = \stdClass::class;

        $marketingList = new MarketingList();
        $marketingList->setSegment($segment);
        $marketingList->setType($type);
        $marketingList->setEntity($entity);

        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setSenderEmail(reset($to));

        $itCount = count($iterable);
        $this->marketingListProvider->expects($this->once())
            ->method('getEntitiesIterator')
            ->willReturn($iterable);

        $manager = $this->createMock(EntityManager::class);
        $this->doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);
        $manager->expects($this->never())
            ->method('flush');
        $manager->expects($this->exactly($itCount))
            ->method('beginTransaction');
        $manager->expects($this->exactly($itCount))
            ->method('rollback');

        $fields = ['email'];
        $this->assertFieldsCall($fields, $marketingList);
        if ($itCount) {
            $this->contactInformationFieldsProvider->expects($this->exactly($itCount * 2))
                ->method('getTypedFieldsValues')
                ->with($fields, $this->anything())
                ->willReturn($to);

            $this->statisticsConnector->expects($this->exactly($itCount))
                ->method('getStatisticsRecord')
                ->with($this->identicalTo($campaign), $this->isInstanceOf(\stdClass::class))
                ->willThrowException(new \Exception('Error'));

            $this->logger->expects($this->exactly($itCount))
                ->method('error');
        }

        $this->transport->expects($this->exactly($itCount))
            ->method('send');

        $this->transportProvider->expects($this->once())
            ->method('getTransportByName')
            ->willReturn($this->transport);

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send();
    }

    private function assertFieldsCall(array $fields, MarketingList $marketingList)
    {
        $this->contactInformationFieldsProvider->expects($this->once())
            ->method('getMarketingListTypedFields')
            ->with($marketingList, ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL)
            ->willReturn($fields);
    }

    public function sendDataProvider(): array
    {
        $entity = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getId'])
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
