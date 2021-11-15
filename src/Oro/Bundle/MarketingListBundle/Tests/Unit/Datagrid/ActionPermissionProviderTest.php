<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\MarketingListBundle\Datagrid\ActionPermissionProvider;

class ActionPermissionProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ActionPermissionProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new ActionPermissionProvider();
    }

    /**
     * @dataProvider permissionsDataProvider
     */
    public function testGetMarketingListItemPermissions(bool $isSubscribed, array $actions, array $expected)
    {
        $record = $this->createMock(ResultRecordInterface::class);

        $record->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('subscribed'))
            ->willReturn($isSubscribed);

        $this->assertEquals(
            $expected,
            $this->provider->getMarketingListItemPermissions($record, $actions)
        );
    }

    public function permissionsDataProvider(): array
    {
        return [
            [false, [], ['subscribe' => true, 'unsubscribe' => false]],
            [true, [], ['subscribe' => false, 'unsubscribe' => true]],
            [true, ['view' => []], ['view' => true, 'subscribe' => false, 'unsubscribe' => true]]
        ];
    }
}
