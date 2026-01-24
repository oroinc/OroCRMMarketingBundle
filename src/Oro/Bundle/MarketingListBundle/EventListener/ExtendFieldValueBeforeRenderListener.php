<?php

namespace Oro\Bundle\MarketingListBundle\EventListener;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Event\ValueRenderEvent;

/**
 * Listens to field value render events and applies contact information field formatting for marketing lists.
 */
class ExtendFieldValueBeforeRenderListener
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var array
     */
    protected $contactInformationMap;

    public function __construct(ConfigProvider $configProvider, array $contactInformationMap)
    {
        $this->configProvider        = $configProvider;
        $this->contactInformationMap = $contactInformationMap;
    }

    public function beforeValueRender(ValueRenderEvent $event)
    {
        $fieldConfig            = $this->configProvider->getConfigById($event->getFieldConfigId());
        $contactInformationType = $fieldConfig->get('contact_information');

        // if some contact information type is defined -- applies proper template for its value
        if (null !== $contactInformationType
            && isset($this->contactInformationMap[$contactInformationType])
        ) {
            if ($event->getFieldValue() instanceof Collection) {
                return;
            }

            $event->setFieldViewValue(
                [
                    'value'    => $event->getFieldValue(),
                    'entity'   => $event->getEntity(),
                    'template' => $this->contactInformationMap[$contactInformationType],
                ]
            );
        }
    }
}
