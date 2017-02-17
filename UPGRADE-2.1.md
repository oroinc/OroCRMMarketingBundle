UPGRADE FROM 2.0 to 2.1
========================

CampaignBundle
--------------
- Method `getCampaignsByCloseRevenue` was removed from `Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository`.
  Use `Oro\Bundle\CampaignBundle\Dashboard\CampaignDataProvider::getCampaignsByCloseRevenueData` instead

MarketingListBundle
-------------------
- Class `Oro\Bundle\MarketingListBundle\Provider\MarketingListProvider`
    - changed the return type of `getMarketingListEntitiesIterator` method from `BufferedQueryResultIterator` to `\Iterator`
