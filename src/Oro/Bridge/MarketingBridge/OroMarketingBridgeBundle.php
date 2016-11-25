<?php

namespace Oro\Bridge\MarketingBridge;

use Oro\Bridge\MarketingBridge\DependencyInjection\OroMarketingBridgeExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroMarketingBridgeBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroMarketingBridgeExtension();
        }

        return $this->extension;
    }
}
