<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\Datagrid;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\MarketingListBundle\Datagrid\GridMarketingListTypeProvider;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class GridMarketingListTypeProviderTest extends WebTestCase
{
    /**
     * @var GridMarketingListTypeProvider
     */
    protected $provider;

    /**
     * @var Registry
     */
    protected $registry;

    protected function setUp(): void
    {
        $this->initClient();

        $this->registry = $this->getContainer()->get('doctrine');
        $this->provider = new GridMarketingListTypeProvider($this->registry);
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
