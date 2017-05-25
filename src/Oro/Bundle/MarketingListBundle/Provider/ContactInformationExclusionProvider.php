<?php

namespace Oro\Bundle\MarketingListBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\AbstractExclusionProvider;
use Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface;

/**
 * Provide exclude logic to filter entities with "contact_information" data
 */
class ContactInformationExclusionProvider extends AbstractExclusionProvider
{
    /**
     * @var VirtualFieldProviderInterface
     */
    protected $virtualFieldProvider;

    /**
     * @param VirtualFieldProviderInterface $virtualFieldProvider
     */
    public function __construct(
        VirtualFieldProviderInterface $virtualFieldProvider
    ) {
        $this->virtualFieldProvider = $virtualFieldProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnoredEntity($className)
    {
        if ($this->virtualFieldProvider->isVirtualField($className, 'contactInformation')) {
            return false;
        }

        return true;
    }
}
