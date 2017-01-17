<?php
namespace Oro\Bundle\SalesBundle\Tests\Functional\Widget;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\DashboardBundle\Tests\Functional\AbstractWidgetTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;

/**
 * @dbIsolationPerTest
 */
class CampaignByCloseRevenueTest extends AbstractWidgetTestCase
{
    /** @var  Widget */
    protected $widget;

    public function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->loadFixtures([
            'Oro\Bundle\CampaignBundle\Tests\Functional\Fixture\LoadCampaignByCloseRevenueWidgetFixture'
        ]);

        $this->widget = $this->getReference('widget_campaigns_by_close_revenue');
    }
    public function testGetWidgetConfigureDialog()
    {
        $this->getConfigureDialog();
    }

    /**
     * @depends      testGetWidgetConfigureDialog
     * @dataProvider widgetProvider
     * @param $requestData
     */
    public function testDateRangeAllTypeFilter($requestData)
    {
        $this->configureWidget($this->widget, $requestData['widgetConfig']);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_campaign_dashboard_campaigns_by_close_revenue_chart',
                [
                    'widget' => 'campaigns_by_close_revenue',
                    '_widgetId' => $this->widget->getId()
                ]
            )
        );
        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, "Failed in getting widget view !");
        $this->assertNotEmpty($crawler->html());

        $this->assertContains($requestData['expectedResultCount'], $crawler->html());
    }

    protected function getConfigureDialog()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_configure',
                ['id' => $this->widget->getId(), '_widgetContainer' => 'dialog']
            )
        );
        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in getting configure widget dialog window !');
    }

    /**
     * @return array
     */
    public function widgetProvider()
    {
        return [
            'Closed lost opportunities' => [
                [
                    'widgetConfig' => [
                        'campaigns_by_close_revenue[dateRange][part]'   => 'value',
                        'campaigns_by_close_revenue[dateRange][type]'   => AbstractDateFilterType::TYPE_ALL_TIME,
                    ],
                    'expectedResultCount' => 'No data found'
                ],
            ]
        ];
    }
}
