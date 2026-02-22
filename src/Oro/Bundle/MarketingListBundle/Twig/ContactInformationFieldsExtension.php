<?php

namespace Oro\Bundle\MarketingListBundle\Twig;

use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function to extract contact information from an entity:
 *   - get_contact_information_fields_info
 */
class ContactInformationFieldsExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    #[\Override]
    public function getFunctions()
    {
        return [
            new TwigFunction('get_contact_information_fields_info', [$this, 'getContactInformationFieldsInfo'])
        ];
    }

    public function getContactInformationFieldsInfo(?string $entityClass): array
    {
        if (!$entityClass) {
            return [];
        }

        return $this->getHelper()->getEntityContactInformationFieldsInfo($entityClass);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            ContactInformationFieldHelper::class
        ];
    }

    private function getHelper(): ContactInformationFieldHelper
    {
        return $this->container->get(ContactInformationFieldHelper::class);
    }
}
