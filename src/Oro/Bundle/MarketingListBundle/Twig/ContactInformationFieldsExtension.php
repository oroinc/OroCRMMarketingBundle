<?php

namespace Oro\Bundle\MarketingListBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;

class ContactInformationFieldsExtension extends \Twig_Extension
{
    const NAME = 'oro_marketing_list_contact_information_fields';

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
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
            new \Twig_SimpleFunction(
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
    public function getName()
    {
        return self::NAME;
    }
}
