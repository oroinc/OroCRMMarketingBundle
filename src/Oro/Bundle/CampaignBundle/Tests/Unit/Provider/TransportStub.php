<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Provider;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Transport\TransportInterface;
use Oro\Bundle\CampaignBundle\Transport\VisibilityTransportInterface;

class TransportStub implements TransportInterface, VisibilityTransportInterface
{
    #[\Override]
    public function send(EmailCampaign $campaign, object $entity, array $from, array $to)
    {
    }

    #[\Override]
    public function getName()
    {
    }

    #[\Override]
    public function getLabel()
    {
    }

    #[\Override]
    public function getSettingsFormType()
    {
    }

    #[\Override]
    public function getSettingsEntityFQCN()
    {
    }

    #[\Override]
    public function isVisibleInForm()
    {
    }
}
