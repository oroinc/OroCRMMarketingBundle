<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Transport;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\InternalTransportSettings;
use Oro\Bundle\CampaignBundle\Transport\EmailTransport;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Sender\EmailModelSender;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class EmailTransportTest extends \PHPUnit\Framework\TestCase
{
    /** @var EmailModelSender|\PHPUnit\Framework\MockObject\MockObject */
    private $emailModelSender;

    /** @var EmailRenderer|\PHPUnit\Framework\MockObject\MockObject */
    private $emailRenderer;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var EmailAddressHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $emailAddressHelper;

    /** @var EmailTransport */
    private $transport;

    protected function setUp(): void
    {
        $this->emailModelSender = $this->createMock(EmailModelSender::class);
        $this->emailRenderer = $this->createMock(EmailRenderer::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->emailAddressHelper = $this->createMock(EmailAddressHelper::class);

        $this->transport = new EmailTransport(
            $this->emailModelSender,
            $this->emailRenderer,
            $this->doctrineHelper,
            $this->emailAddressHelper
        );
    }

    /**
     * @dataProvider sendDataProvider
     */
    public function testSend(
        string|int|null $id,
        array $from,
        array $to,
        ?string $subject,
        ?string $body
    ): void {
        $entity = new \stdClass();

        $organization = new Organization();
        $organization->setId(12);
        $emails = array_keys($from);

        $this->doctrineHelper->expects(self::once())
            ->method('getSingleEntityIdentifier')
            ->willReturn($id);

        $this->emailAddressHelper->expects(self::once())
            ->method('buildFullEmailAddress')
            ->willReturn(sprintf('%s <%s>', reset($emails), reset($from)));

        $marketingList = new MarketingList();
        $marketingList->setEntity(get_class($entity));

        $template = new EmailTemplate();
        $template->setType('html');
        $settings = new InternalTransportSettings();
        $settings
            ->setTemplate($template);
        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setTransportSettings($settings)
            ->setOrganization($organization);

        $this->emailRenderer->expects(self::once())
            ->method('compileMessage')
            ->willReturn([$subject, $body]);

        $emailModel = new Email();
        $emailModel
            ->setFrom(sprintf('%s <%s>', reset($emails), reset($from)))
            ->setType($template->getType())
            ->setEntityClass(get_class($entity))
            ->setEntityId($id)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->setOrganization($organization);

        $this->emailModelSender->expects(self::once())
            ->method('send')
            ->with($emailModel);

        $this->transport->send($campaign, $entity, $from, $to);
    }

    public function sendDataProvider(): array
    {
        return [
            [1, ['sender@example.com' => 'Sender Name'], [], 'subject', 'body'],
            [null, ['sender@example.com' => 'Sender Name'], [], 'subject', 'body'],
            ['string', ['sender@example.com' => 'Sender Name'], [], 'subject', 'body'],
            [1, ['sender@example.com' => 'Sender Name'], ['test@example.com'], 'subject', 'body'],
            [1, ['sender@example.com' => 'Sender Name'], ['test@example.com'], null, 'body'],
            [1, ['sender@example.com' => 'Sender Name'], ['test@example.com'], 'subject', null],
            [1, ['sender@example.com' => 'Sender Name'], ['test@example.com'], null, null],
            [1, ['sender@example.com' => 'Sender Name'], ['test@example.com'], null, null],
            [1, ['sender@example.com' => 'Sender Name'], [null], null, null],
        ];
    }

    public function testFromEmpty(): void
    {
        $organization = new Organization();
        $organization->setId(13);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sender email and name is empty');

        $entity = new \stdClass();

        $this->doctrineHelper->expects(self::once())
            ->method('getSingleEntityIdentifier')
            ->willReturn(1);

        $marketingList = new MarketingList();
        $marketingList->setEntity($entity);

        $template = new EmailTemplate();
        $template->setType('html');
        $settings = new InternalTransportSettings();
        $settings
            ->setTemplate($template);
        $campaign = new EmailCampaign();
        $campaign
            ->setMarketingList($marketingList)
            ->setTransportSettings($settings)
            ->setOrganization($organization);

        $this->emailRenderer->expects(self::never())
            ->method('compileMessage');

        $this->transport->send($campaign, $entity, [], []);
    }
}
