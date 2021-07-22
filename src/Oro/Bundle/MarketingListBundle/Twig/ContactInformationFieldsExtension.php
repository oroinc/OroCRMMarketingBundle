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
    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContactInformationFieldHelper
     */
    protected function getHelper()
    {
        return $this->container->get('oro_marketing_list.contact_information_field_helper');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'get_contact_information_fields_info',
                [$this, 'getContactInformationFieldsInfo']
            )
        ];
    }

    /**
     * @param string $entityClass
     *
     * @return array
     */
    public function getContactInformationFieldsInfo($entityClass)
    {
        if (!$entityClass) {
            return [];
        }

        return $this->getHelper()->getEntityContactInformationFieldsInfo($entityClass);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_marketing_list.contact_information_field_helper' => ContactInformationFieldHelper::class,
        ];
    }
}
