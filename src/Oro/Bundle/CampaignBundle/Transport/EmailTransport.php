<?php

namespace Oro\Bundle\CampaignBundle\Transport;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Form\Type\InternalTransportSettingsType;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Sender\EmailModelSender;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Implements the transport to send campaigns emails.
 */
class EmailTransport implements TransportInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const NAME = 'internal';

    private EmailModelSender $emailModelSender;

    private EmailRenderer $emailRenderer;

    private DoctrineHelper $doctrineHelper;

    private EmailAddressHelper $emailAddressHelper;

    public function __construct(
        EmailModelSender $emailModelSender,
        EmailRenderer $emailRenderer,
        DoctrineHelper $doctrineHelper,
        EmailAddressHelper $emailAddressHelper
    ) {
        $this->emailModelSender = $emailModelSender;
        $this->emailRenderer = $emailRenderer;
        $this->doctrineHelper = $doctrineHelper;
        $this->emailAddressHelper = $emailAddressHelper;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function send(EmailCampaign $campaign, object $entity, array $from, array $to)
    {
        $entityId = $this->doctrineHelper->getSingleEntityIdentifier($entity);
        $marketingList = $campaign->getMarketingList();
        $fromAddress = $this->buildFullEmailAddress($from);

        /** @var EmailTemplate $template */
        $template = $campaign->getTransportSettings()->getSettingsBag()->get('template');
        [$subjectRendered, $templateRendered] = $this->emailRenderer->compileMessage(
            $template,
            ['entity' => $entity]
        );

        $emailModel = new Email();
        $emailModel
            ->setType($template->getType())
            ->setFrom($fromAddress)
            ->setEntityClass($marketingList->getEntity())
            ->setEntityId($entityId)
            ->setTo($to)
            ->setSubject($subjectRendered)
            ->setBody($templateRendered)
            ->setOrganization($campaign->getOrganization());

        try {
            $this->emailModelSender->send($emailModel, null, false);
        } catch (\RuntimeException $exception) {
            $this->logger->error(
                sprintf(
                    'Failed to send email model to %s: %s',
                    implode(', ', $emailModel->getTo()),
                    $exception->getMessage()
                ),
                ['exception' => $exception, 'emailModel' => $emailModel]
            );
        }
    }

    /**
     * @param array $from
     *
     * @return string
     */
    protected function buildFullEmailAddress(array $from)
    {
        foreach ($from as $email => $name) {
            return $this->emailAddressHelper->buildFullEmailAddress($email, $name);
        }

        throw new \InvalidArgumentException('Sender email and name is empty');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.campaign.emailcampaign.transport.' . self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return InternalTransportSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'Oro\Bundle\CampaignBundle\Entity\InternalTransportSettings';
    }
}
