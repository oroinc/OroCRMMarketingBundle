UPGRADE FROM 2.0 to 2.1
========================

####General
- Changed minimum required php version to 7.0
- Updated dependency to [fxpio/composer-asset-plugin](https://github.com/fxpio/composer-asset-plugin) composer plugin to version 1.3.
- Composer updated to version 1.4.

```
    composer self-update
    composer global require "fxp/composer-asset-plugin"
```

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
