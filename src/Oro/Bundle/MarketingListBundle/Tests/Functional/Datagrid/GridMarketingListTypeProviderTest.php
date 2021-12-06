<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\Datagrid;

use Oro\Bundle\MarketingListBundle\Datagrid\GridMarketingListTypeProvider;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class GridMarketingListTypeProviderTest extends WebTestCase
{
    /** @var GridMarketingListTypeProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->initClient();

        $this->provider = new GridMarketingListTypeProvider($this->getContainer()->get('doctrine'));
    }

    public function testGetListTypeChoices()
    {
        $types = $this->provider->getListTypeChoices();
        $this->assertIsArray($types);
        foreach ($types as $name => $label) {
            $this->assertIsString($name);
            $this->assertIsString($label);
        }
    }
}
