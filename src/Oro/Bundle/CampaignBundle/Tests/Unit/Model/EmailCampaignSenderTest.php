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

class EmailCampaignSenderTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_ID = 1;

    /** @var EmailCampaignSender */
    protected $sender;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $marketingListProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $statisticsConnector;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $contactInformationFieldsProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $transport;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $transportProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $validator;

    protected function setUp()
    {
        $this->marketingListProvider = $this->createMock(MarketingListProvider::class);
        $this->statisticsConnector = $this->createMock(EmailCampaignStatisticsConnector::class);
        $this->contactInformationFieldsProvider = $this->createMock(ContactInformationFieldsProvider::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->transport = $this->createMock(TransportInterface::class);
        $this->transportProvider = $this->createMock(EmailTransportProvider::class);

        $this->sender = new EmailCampaignSender(
            $this->marketingListProvider,
            $this->createMock(ConfigManager::class),
            $this->statisticsConnector,
            $this->contactInformationFieldsProvider,
            $this->registry,
            $this->transportProvider
        );

        $this->validator = $this->createMock('Symfony\Component\Validator\ValidatorInterface');
        $this->sender->setValidator($this->validator);
    }

    protected function tearDown()
    {
        unset($this->sender);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Transport is required to perform send
     */
    public function testAssertTransport()
    {
        $campaign = new EmailCampaign();

        $this->sender->send($campaign);
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

        $this->sender->setLogger($this->createMock(LoggerInterface::class));
        $this->sender->send($campaign);
    }

    public function testNotSent()
    {
        $segment = new Segment();

        $marketingList = new MarketingList();
        $marketingList->setSegment($segment);

        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setSenderName('test')
            ->setSenderEmail('test@localhost');

        $this->marketingListProvider
            ->expects($this->once())
            ->method('getMarketingListEntitiesIterator')
            ->will($this->returnValue(null));

        $this->transport->expects($this->never())
            ->method('send');

        $this->transportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->will($this->returnValue($this->transport));

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send($campaign);
    }

    /**
     * @param array  $iterable
     * @param array  $to
     * @param object $type
     *
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
            ->method('getMarketingListEntitiesIterator')
            ->will($this->returnValue($iterable));

        $manager = $this->createMock(EntityManager::class);
        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));
        $manager->expects($this->atLeastOnce())
            ->method('flush');
        $manager->expects($this->exactly($itCount))
            ->method('beginTransaction');
        $manager->expects($this->exactly($itCount))
            ->method('commit');

        $fields = ['email' => 'email'];
        $this->assertFieldsCall($fields, $marketingList);
        if ($itCount) {
            $this->contactInformationFieldsProvider
                ->expects($this->exactly($itCount))
                ->method('getTypedFieldsValues')
                ->with(
                    $this->equalTo(array_keys($fields)),
                    $this->isType('object')
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
        $this->sender->send($campaign);
    }

    /**
     * @param array  $iterable
     * @param array  $to
     * @param object $type
     *
     * @dataProvider sendDataProvider
     */
    public function testSendError($iterable, $to, $type)
    {
        $segment = new Segment();
        $entity = '\stdClass';
        $to = array_keys($to);

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
            ->method('getMarketingListEntitiesIterator')
            ->will($this->returnValue($iterable));

        $manager = $manager = $this->createMock(EntityManager::class);
        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($manager));
        $manager->expects($this->once())
            ->method('persist')
            ->with($campaign);
        $manager->expects($this->atLeastOnce())
            ->method('flush');
        $manager->expects($this->exactly($itCount))
            ->method('beginTransaction');
        $manager->expects($this->exactly($itCount))
            ->method('rollback');

        $fields = ['email' => 'email'];
        $this->assertFieldsCall($fields, $marketingList);
        if ($itCount) {
            $this->contactInformationFieldsProvider
                ->expects($this->exactly($itCount))
                ->method('getTypedFieldsValues')
                ->with(
                    $this->equalTo(array_keys($fields)),
                    $this->isType('object')
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

            $logger = $this->createMock(LoggerInterface::class);
            $logger->expects($this->exactly($itCount))
                ->method('error');

            $this->sender->setLogger($logger);
        }

        $this->transport->expects($this->exactly($itCount))
            ->method('send');

        $this->transportProvider
            ->expects($this->once())
            ->method('getTransportByName')
            ->will($this->returnValue($this->transport));

        $this->sender->setEmailCampaign($campaign);
        $this->sender->send($campaign);
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
            [[$entity, $entity], [], $manualType],
            [[$entity], [], $manualType],
            [[], [], $manualType],
            [[], ['mail@example.com'], $manualType],
            [[$entity], ['mail@example.com'], $manualType],
            [[$entity, $entity], ['mail@example.com'], $manualType],

            [[$entity, $entity], [], $segmentBasedType],
            [[$entity], [], $segmentBasedType],
            [[], [], $segmentBasedType],
            [[], ['mail@example.com'], $segmentBasedType],
            [[$entity], ['mail@example.com'], $segmentBasedType],
            [[$entity, $entity], ['mail@example.com'], $segmentBasedType],
        ];
    }
}
