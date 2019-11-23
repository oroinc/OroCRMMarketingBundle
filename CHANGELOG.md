Please refer first to [UPGRADE.md](UPGRADE.md) for the most important items that should be addressed before attempting to upgrade or during the upgrade of a vanilla Oro application.

The current file describes significant changes in the code that may affect the upgrade of your customizations.

## 4.1.0-beta

### Removed

#### All Bundles
* All `*.class` parameters were removed from the dependency injection container.

#### MarketingListBundle
* The deprecated constant `Oro\Bundle\MarketingListBundle\Datagrid\Extension\MarketingListExtension::NAME_PATH` was removed.

## 4.0.0 (2019-07-31)
[Show detailed list of changes](incompatibilities-4-0.md)

### Changed
#### ShoppingListBundle

* The `removeAction` in `Oro\Bundle\MarketingListBundle\Controller\Api\Rest\MarketingListRemovedItemController` now support only `DELETE` method insteadof `POST`.

## 4.0.0-rc (2019-05-29)
[Show detailed list of changes](incompatibilities-4-0-rc.md)

## 4.0.0-beta (2019-03-28)

### Changed
#### MarketingListBundle
* In `Oro\Bundle\MarketingListBundle\Controller\Api\Rest\MarketingListRemovedItemController::removeAction` 
 (`/marketinglist/{marketingList}/remove/{id}` path)
 action the request method was changed to POST. 
* In `Oro\Bundle\MarketingListBundle\Controller\Api\Rest\MarketingListRemovedItemController::unremoveAction` 
 (`/marketinglist/{marketingList}/unremove/{id}` path)
 action the request method was changed to POST. 

## 3.0.0-rc (2018-05-31)
[Show detailed list of changes](incompatibilities-3-0-rc.md)

## 3.0.0-beta (2018-03-30)
[Show detailed list of changes](incompatibilities-3-0-beta.md)

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
