## 2.6.0 (2018-01-31)
[Show detailed list of changes](incompatibilities-2-6.md)

## 2.5.0 (2017-11-30)
[Show detailed list of changes](incompatibilities-2-5.md)

## 2.4.0 (2017-09-29)
[Show detailed list of changes](incompatibilities-2-4.md)

## 2.3.0 (2017-07-28)
[Show detailed list of changes](incompatibilities-2-3.md)

## 2.2.0 (2017-05-31)
[Show detailed list of changes](incompatibilities-2-2.md)

## 2.1.0 (2017-03-30)
[Show detailed list of changes](incompatibilities-2-1.md)

### Changed
#### MarketingListBundle
* class `MarketingListProvider`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.1.0/src/Oro/Bundle/MarketingListBundle/Provider/MarketingListProvider.php "Oro\Bundle\MarketingListBundle\Provider\MarketingListProvider")</sup>
    - changed the return type of `getMarketingListEntitiesIterator` method from `BufferedQueryResultIterator` to `\Iterator`
* the `oro_marketing_list.twig.extension.contact_information_fields` service was marked as `private`
### Removed
#### CampaignBundle
* method `getCampaignsByCloseRevenue` was removed from `CampaignRepository`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.1.0/src/Oro/Bundle/CampaignBundle/Entity/Repository/CampaignRepository.php "Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository")</sup>. Use `CampaignDataProvider::getCampaignsByCloseRevenueData`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.1.0/src/Oro/Bundle/CampaignBundle/Dashboard/CampaignDataProvider.php#L81 "Oro\Bundle\CampaignBundle\Dashboard\CampaignDataProvider::getCampaignsByCloseRevenueData")</sup> instead
#### MarketingListBundle
* removed the following parameters from DIC:
    - `oro_marketing_list.twig.extension.contact_information_fields.class`
