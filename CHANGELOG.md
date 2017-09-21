## 2.4.0 (Unreleased)
[Show detailed list of changes](file-incompatibilities-2-4-0.md)

## 2.3.0 (2017-07-28)
[Show detailed list of changes](file-incompatibilities-2-3-0.md)

## 2.2.0 (2017-05-31)
[Show detailed list of changes](file-incompatibilities-2-2-0.md)

## 2.1.0 (2017-03-30)
[Show detailed list of changes](file-incompatibilities-2-1-0.md)

### Changed
* **MarketingListBundle** class `Oro\Bundle\MarketingListBundle\Provider\MarketingListProvider`
    - changed the return type of `getMarketingListEntitiesIterator` method from `BufferedQueryResultIterator` to `\Iterator`
* **MarketingListBundle** the `oro_marketing_list.twig.extension.contact_information_fields` service was marked as `private`
### Removed
* **CampaignBundle:** method `getCampaignsByCloseRevenue` was removed from `Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository`. Use `Oro\Bundle\CampaignBundle\Dashboard\CampaignDataProvider::getCampaignsByCloseRevenueData` instead

* **MarketingListBundle** removed the following parameters from DIC:
    - `oro_marketing_list.twig.extension.contact_information_fields.class`
