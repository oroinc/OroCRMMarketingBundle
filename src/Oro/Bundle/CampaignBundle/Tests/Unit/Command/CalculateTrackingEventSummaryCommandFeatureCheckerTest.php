<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Command;

use Oro\Bundle\CampaignBundle\Command\CalculateTrackingEventSummaryCommandFeatureChecker;
use Oro\Bundle\CronBundle\Command\CronCommandFeatureCheckerInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;

class CalculateTrackingEventSummaryCommandFeatureCheckerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CronCommandFeatureCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $innerChecker;

    /** @var FeatureChecker|\PHPUnit\Framework\MockObject\MockObject */
    private $featureChecker;

    /** @var CalculateTrackingEventSummaryCommandFeatureChecker */
    private $checker;

    protected function setUp(): void
    {
        $this->innerChecker = $this->createMock(CronCommandFeatureCheckerInterface::class);
        $this->featureChecker = $this->createMock(FeatureChecker::class);

        $this->checker = new CalculateTrackingEventSummaryCommandFeatureChecker(
            $this->innerChecker,
            $this->featureChecker
        );
    }

    /**
     * @dataProvider isFeatureEnabledForNotCalculateTrackingEventSummaryCommandDataProvider
     */
    public function testIsFeatureEnabledForNotCalculateTrackingEventSummaryCommand(bool $result): void
    {
        $commandName = 'test';

        $this->featureChecker->expects(self::never())
            ->method('isFeatureEnabled');
        $this->innerChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with($commandName)
            ->willReturn($result);

        self::assertSame($result, $this->checker->isFeatureEnabled($commandName));
    }

    public function isFeatureEnabledForNotCalculateTrackingEventSummaryCommandDataProvider(): array
    {
        return [[false], [true]];
    }

    /**
     * @dataProvider isFeatureEnabledForCalculateTrackingEventSummaryCommandDataProvider
     */
    public function testIsFeatureEnabledForCalculateTrackingEventSummaryCommand(array $features, bool $result): void
    {
        $commandName = 'oro:cron:calculate-tracking-event-summary';

        $checkResultMap = [];
        foreach ($features as $featureName => $featureEnabled) {
            $checkResultMap[] = [$featureName, null, $featureEnabled];
        }

        $this->featureChecker->expects(self::atLeastOnce())
            ->method('isFeatureEnabled')
            ->willReturnMap($checkResultMap);
        $this->innerChecker->expects(self::never())
            ->method('isFeatureEnabled');

        self::assertSame($result, $this->checker->isFeatureEnabled($commandName));
    }

    public function isFeatureEnabledForCalculateTrackingEventSummaryCommandDataProvider(): array
    {
        return [
            [['tracking' => false, 'campaign' => false], false],
            [['tracking' => false, 'campaign' => true], true],
            [['tracking' => true, 'campaign' => false], true],
            [['tracking' => true, 'campaign' => true], true],
        ];
    }
}
