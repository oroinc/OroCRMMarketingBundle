## 2.5.0 (Unreleased)
## 2.4.0 (Unreleased)
[Show detailed list of changes](file-incompatibilities-2-4-0.md)

## 2.3.3 (2017-09-19)
## 2.3.2 (2017-08-22)
## 2.3.1 (2017-08-11)
## 2.3.0 (2017-07-28)
[Show detailed list of changes](file-incompatibilities-2-3-0.md)

## 2.2.4 (2017-08-15)
## 2.2.3 (2017-08-11)
## 2.2.2 (2017-07-26)
## 2.2.1 (2017-06-27)
## 2.2.0 (2017-05-31)
[Show detailed list of changes](file-incompatibilities-2-2-0.md)

## 2.1.3 (2017-06-27)
## 2.1.2 (2017-05-26)
## 2.1.1 (2017-05-03)
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

## 2.0.9 (2017-08-12)
## 2.0.8 (2017-07-26)
## 2.0.7 (2017-06-27)
## 2.0.6 (2017-05-30)
## 2.0.5 (2017-05-12)
## 2.0.4 (2017-04-05)
## 2.0.3 (2017-03-17)
## 2.0.2 (2017-03-02)
## 2.0.1 (2017-02-06)
## 2.0.0 (2017-01-16)
