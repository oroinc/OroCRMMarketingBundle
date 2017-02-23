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
- Removed the following parameters from DIC:
    - `oro_marketing_list.twig.extension.contact_information_fields.class`
- The following services were marked as `private`:
    - `oro_marketing_list.twig.extension.contact_information_fields`
- Class `Oro\Bundle\MarketingListBundle\Twig\ContactInformationFieldsExtension`
    - the construction signature of was changed. Now the constructor has only `ContainerInterface $container` parameter
    - removed property `protected $helper`
