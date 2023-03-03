<?php

namespace Oro\Bundle\MarketingListBundle\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\EntityBundle\Provider\AbstractExclusionProvider;
use Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

/**
 * Provide exclude logic to filter entities with "contact_information" data
 */
class ContactInformationExclusionProvider extends AbstractExclusionProvider
{
    /**
     * @var ConfigProvider
     */
    protected $entityConfigProvider;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var VirtualFieldProviderInterface
     */
    protected $virtualFieldProvider;

    public function __construct(
        VirtualFieldProviderInterface $virtualFieldProvider,
        ConfigProvider $entityConfigProvider,
        ManagerRegistry $managerRegistry
    ) {
        $this->virtualFieldProvider = $virtualFieldProvider;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnoredEntity($className)
    {
        if ($this->virtualFieldProvider->isVirtualField($className, 'contactInformation')) {
            return false;
        }

        /**
         *  According to acceptance criteria in CRM-7491, all addresses must be removed from list
         */
        if (is_a($className, AbstractAddress::class, true)) {
            return true;
        }

        $managerForClass = $this->managerRegistry->getManagerForClass($className);
        if (null === $managerForClass) {
            return true;
        }
        /** @var ClassMetadata $metadata */
        $metadata = $managerForClass->getClassMetadata($className);
        foreach ($metadata->getFieldNames() as $fieldName) {
            if (!$this->entityConfigProvider->hasConfig($className, $fieldName)) {
                continue;
            }
            $fieldConfig = $this->entityConfigProvider->getConfig($className, $fieldName);
            if ($fieldConfig->has('contact_information')) {
                return false;
            }
        }

        return true;
    }
}
