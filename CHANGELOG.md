The upgrade instructions are available at [Oro documentation website](https://doc.oroinc.com/master/backend/setup/upgrade-to-new-version/).

The current file describes significant changes in the code that may affect the upgrade of your customizations.

## Changes in the Marketing package versions

- [5.1.0](#510-2023-03-31)
- [5.0.0](#500-2022-01-26)
- [4.2.0](#420-2020-01-29)
- [4.1.0](#410-2020-01-31)
- [4.0.0](#400-2019-07-31)
- [3.0.0](#300-2018-07-27)
- [2.6.0](#260-2018-01-31)
- [2.5.0](#250-2017-11-30)
- [2.4.0](#240-2017-09-29)
- [2.3.0](#230-2017-07-28)
- [2.2.0](#220-2017-05-31)
- [2.1.0](#210-2017-03-30)


## 5.1.0 (2023-03-31)
[Show detailed list of changes](incompatibilities-5-1.md)

### Added

#### CampaignBundle
* Added entity name providers for `Campaign` and `EmailCampaign` entities

#### MarketingListBundle
* Added entity name provider for `MarketingList` entity


## 5.0.0 (2022-01-26)
[Show detailed list of changes](incompatibilities-5-0.md)

## 4.2.0 (2020-01-29)
[Show detailed list of changes](incompatibilities-4-2.md)

### Changed

#### MarketingActivityBundle
* The name for `/api/matypes` REST API resource was changed to `/api/marketingactivitytypes`.

## 4.1.0 (2020-01-31)
[Show detailed list of changes](incompatibilities-4-1.md)

### Removed
* `*.class` parameters for all entities were removed from the dependency injection container.
The entity class names should be used directly, e.g. `'Oro\Bundle\EmailBundle\Entity\Email'`
instead of `'%oro_email.email.entity.class%'` (in service definitions, datagrid config files, placeholders, etc.), and
`\Oro\Bundle\EmailBundle\Entity\Email::class` instead of `$container->getParameter('oro_email.email.entity.class')`
(in PHP code).
* All `*.class` parameters for service definitions were removed from the dependency injection container.

#### CampaignBundle
* The deprecated constant `Oro\Bundle\CampaignBundle\EventListener\CampaignStatisticDatagridListener::PATH_NAME` was removed.
* The deprecated constant `Oro\Bundle\CampaignBundle\EventListener\CampaignStatisticGroupingListener::PATH_NAME` was removed.

#### MarketingListBundle
* The deprecated constant `Oro\Bundle\MarketingListBundle\Datagrid\Extension\MarketingListExtension::NAME_PATH` was removed.

## 4.0.0 (2019-07-31)
[Show detailed list of changes](incompatibilities-4-0.md)

### Changed

#### ShoppingListBundle

* The `removeAction` in `Oro\Bundle\MarketingListBundle\Controller\Api\Rest\MarketingListRemovedItemController` now support only `DELETE` method insteadof `POST`.

#### MarketingListBundle
* In `Oro\Bundle\MarketingListBundle\Controller\Api\Rest\MarketingListRemovedItemController::removeAction` 
 (`/marketinglist/{marketingList}/remove/{id}` path)
 action the request method was changed to POST. 
* In `Oro\Bundle\MarketingListBundle\Controller\Api\Rest\MarketingListRemovedItemController::unremoveAction` 
 (`/marketinglist/{marketingList}/unremove/{id}` path)
 action the request method was changed to POST. 

## 3.0.0 (2018-07-27)

[Show detailed list of changes](incompatibilities-3-0.md)

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
