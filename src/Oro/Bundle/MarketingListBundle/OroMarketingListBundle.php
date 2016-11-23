<?php

namespace Oro\Bundle\MarketingListBundle;

use Oro\Bundle\MarketingListBundle\DependencyInjection\OroMarketingListExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroMarketingListBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroMarketingListExtension();
        }

        return $this->extension;
    }
}
