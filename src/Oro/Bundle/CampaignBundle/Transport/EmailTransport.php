<?php

namespace Oro\Bundle\CampaignBundle\Transport;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Form\Type\InternalTransportSettingsType;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

/**
 * Implements the transport to send campaigns emails.
 */
class EmailTransport implements TransportInterface
{
    const NAME = 'internal';

    /**
     * @var Processor $processor
     */
    protected $processor;

    /**
     * @var EmailRenderer
     */
    protected $emailRenderer;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var EmailAddressHelper
     */
    protected $emailAddressHelper;

    public function __construct(
        Processor $processor,
        EmailRenderer $emailRenderer,
        DoctrineHelper $doctrineHelper,
        EmailAddressHelper $emailAddressHelper
    ) {
        $this->processor          = $processor;
        $this->emailRenderer      = $emailRenderer;
        $this->doctrineHelper     = $doctrineHelper;
        $this->emailAddressHelper = $emailAddressHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function send(EmailCampaign $campaign, $entity, array $from, array $to)
    {
        $entityId = $this->doctrineHelper->getSingleEntityIdentifier($entity);
        $marketingList = $campaign->getMarketingList();
        $fromAddress = $this->buildFullEmailAddress($from);

        /** @var EmailTemplate $template */
        $template = $campaign->getTransportSettings()->getSettingsBag()->get('template');
        list($subjectRendered, $templateRendered) = $this->emailRenderer->compileMessage(
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

        $this->processor->process($emailModel, null, false);
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
