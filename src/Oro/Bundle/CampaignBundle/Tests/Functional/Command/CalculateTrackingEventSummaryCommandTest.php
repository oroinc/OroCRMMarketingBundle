<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Command;

use Oro\Bundle\CampaignBundle\Command\CalculateTrackingEventSummaryCommand;
use Oro\Bundle\CampaignBundle\Entity\TrackingEventSummary;
use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadCampaignData;
use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadTrackingEventData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CalculateTrackingEventSummaryCommandTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadCampaignData::class, LoadTrackingEventData::class]);
    }

    public function testReportUpdate()
    {
        $result = $this->runCommand(CalculateTrackingEventSummaryCommand::getDefaultName());

        $expectedMessages = [
            'Campaigns to calculate: 3',
            'Calculating statistic for campaign: Campaign1',
            'Calculating statistic for campaign: Campaign2',
            'Calculating statistic for campaign: Campaign3',
            'Finished campaigns statistic calculation'
        ];

        $this->assertEquals(implode(' ', $expectedMessages), $result);

        $timezone = new \DateTimeZone('UTC');
        $dateOne = new \DateTime('-1 day', $timezone);
        $dateTwo = new \DateTime('-2 days', $timezone);
        $dateThree = new \DateTime('-3 days', $timezone);
        $expectedData = [
            [
                'code' => 'cmp1',
                'name' => 'ev1',
                'visitCount' => 2,
                'loggedAtDate' => $dateThree->format('Y-m-d')
            ],
            [
                'code' => 'cmp1',
                'name' => 'ev1',
                'visitCount' => 1,
                'loggedAtDate' => $dateTwo->format('Y-m-d')
            ],
            [
                'code' => 'cmp1',
                'name' => 'ev1',
                'visitCount' => 1,
                'loggedAtDate' => $dateOne->format('Y-m-d')
            ],
            [
                'code' => 'cmp1',
                'name' => 'ev2',
                'visitCount' => 1,
                'loggedAtDate' => $dateThree->format('Y-m-d')
            ],
            [
                'code' => 'cmp3',
                'name' => 'ev1',
                'visitCount' => 1,
                'loggedAtDate' => $dateOne->format('Y-m-d')
            ],
        ];
        $summaryData = $this->getSummaryData();

        $this->assertEquals($expectedData, $summaryData);
    }

    private function getSummaryData(): array
    {
        return $this->getContainer()->get('doctrine')
            ->getRepository(TrackingEventSummary::class)
            ->createQueryBuilder('q')
            ->select(['q.code', 'q.name', 'q.visitCount', 'DATE(q.loggedAt) as loggedAtDate'])
            ->addOrderBy('q.code, q.name, q.loggedAt')
            ->getQuery()
            ->getArrayResult();
    }
}
