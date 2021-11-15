<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Transport;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\InternalTransportSettings;
use Oro\Bundle\CampaignBundle\Transport\EmailTransport;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

class EmailTransportTest extends \PHPUnit\Framework\TestCase
{
    /** @var Processor|\PHPUnit\Framework\MockObject\MockObject */
    private $processor;

    /** @var EmailRenderer|\PHPUnit\Framework\MockObject\MockObject */
    private $renderer;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $helper;

    /** @var EmailAddressHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $emailHelper;

    /** @var EmailTransport */
    private $transport;

    protected function setUp(): void
    {
        $this->processor = $this->createMock(Processor::class);
        $this->renderer = $this->createMock(EmailRenderer::class);
        $this->helper = $this->createMock(DoctrineHelper::class);
        $this->emailHelper = $this->createMock(EmailAddressHelper::class);

        $this->transport = new EmailTransport($this->processor, $this->renderer, $this->helper, $this->emailHelper);
    }

    /**
     * @dataProvider sendDataProvider
     */
    public function testSend(
        int|string|null $id,
        ?string $entity,
        array $from,
        array $to,
        ?string $subject,
        ?string $body
    ) {
        $emails = array_keys($from);

        $this->helper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->willReturn($id);

        $this->emailHelper->expects($this->once())
            ->method('buildFullEmailAddress')
            ->willReturn(sprintf('%s <%s>', reset($emails), reset($from)));

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
            ->setTransportSettings($settings);

        $this->renderer->expects($this->once())
            ->method('compileMessage')
            ->willReturn([$subject, $body]);

        $emailModel = new Email();
        $emailModel
            ->setFrom(sprintf('%s <%s>', reset($emails), reset($from)))
            ->setType($template->getType())
            ->setEntityClass($entity)
            ->setEntityId($id)
            ->setTo($to)
            ->setSubject($subject)
            ->setBody($body);

        $this->processor->expects($this->once())
            ->method('process')
            ->with($this->equalTo($emailModel));

        $this->transport->send($campaign, $entity, $from, $to);
    }

    public function sendDataProvider(): array
    {
        return [
            [1, \stdClass::class, ['sender@example.com' => 'Sender Name'], [], 'subject', 'body'],
            [null, \stdClass::class, ['sender@example.com' => 'Sender Name'], [], 'subject', 'body'],
            ['string', \stdClass::class, ['sender@example.com' => 'Sender Name'], [], 'subject', 'body'],
            [1, \stdClass::class, ['sender@example.com' => 'Sender Name'], ['test@example.com'], 'subject', 'body'],
            [1, \stdClass::class, ['sender@example.com' => 'Sender Name'], ['test@example.com'], null, 'body'],
            [1, \stdClass::class, ['sender@example.com' => 'Sender Name'], ['test@example.com'], 'subject', null],
            [1, \stdClass::class, ['sender@example.com' => 'Sender Name'], ['test@example.com'], null, null],
            [1, null, ['sender@example.com' => 'Sender Name'], ['test@example.com'], null, null],
            [1, \stdClass::class, ['sender@example.com' => 'Sender Name'], ['test@example.com'], null, null],
            [1, \stdClass::class, ['sender@example.com' => 'Sender Name'], [null], null, null],
        ];
    }

    public function testFromEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sender email and name is empty');

        $entity = new \stdClass();

        $this->helper->expects($this->once())
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
            ->setTransportSettings($settings);

        $this->renderer->expects($this->never())
            ->method('compileMessage');

        $this->transport->send($campaign, $entity, [], []);
    }
}
