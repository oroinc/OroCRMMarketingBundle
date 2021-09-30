- [CampaignBundle](#campaignbundle)
- [MarketingListBundle](#marketinglistbundle)
- [TrackingBundle](#trackingbundle)

CampaignBundle
--------------
* The following methods in class `EmailCampaignController`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/CampaignBundle/Controller/EmailCampaignController.php#L56 "Oro\Bundle\CampaignBundle\Controller\EmailCampaignController")</sup> were changed:
  > - `createAction()`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/CampaignBundle/Controller/EmailCampaignController.php#L56 "Oro\Bundle\CampaignBundle\Controller\EmailCampaignController")</sup>
  > - `createAction(Request $request)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/CampaignBundle/Controller/EmailCampaignController.php#L56 "Oro\Bundle\CampaignBundle\Controller\EmailCampaignController")</sup>

  > - `update(EmailCampaign $entity)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/CampaignBundle/Controller/EmailCampaignController.php#L114 "Oro\Bundle\CampaignBundle\Controller\EmailCampaignController")</sup>
  > - `update(EmailCampaign $entity, Request $request)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/CampaignBundle/Controller/EmailCampaignController.php#L115 "Oro\Bundle\CampaignBundle\Controller\EmailCampaignController")</sup>


MarketingListBundle
-------------------
* The `ContactInformationFieldsExtension::getName`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/MarketingListBundle/Twig/ContactInformationFieldsExtension.php#L65 "Oro\Bundle\MarketingListBundle\Twig\ContactInformationFieldsExtension::getName")</sup> method was removed.

TrackingBundle
--------------
* The `TrackingEventIdentificationPass`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/TrackingBundle/DependencyInjection/Compiler/TrackingEventIdentificationPass.php#L13 "Oro\Bundle\TrackingBundle\DependencyInjection\Compiler\TrackingEventIdentificationPass")</sup> class was removed.
* The `OroTrackingBundle::build`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/TrackingBundle/OroTrackingBundle.php#L14 "Oro\Bundle\TrackingBundle\OroTrackingBundle::build")</sup> method was removed.
* The `TrackingEventIdentificationProvider::addProvider`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/TrackingBundle/Provider/TrackingEventIdentificationProvider.php#L16 "Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentificationProvider::addProvider")</sup> method was removed.
* The `DataNormalizer::setEntityName($entityName)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/TrackingBundle/ImportExport/DataNormalizer.php#L23 "Oro\Bundle\TrackingBundle\ImportExport\DataNormalizer")</sup> method was changed to `DataNormalizer::setEntityName($entityName)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/TrackingBundle/ImportExport/DataNormalizer.php#L23 "Oro\Bundle\TrackingBundle\ImportExport\DataNormalizer")</sup>
* The `ConfigListener::__construct(ConfigManager $configManager, Router $router, $logsDir)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/TrackingBundle/EventListener/ConfigListener.php#L52 "Oro\Bundle\TrackingBundle\EventListener\ConfigListener")</sup> method was changed to `ConfigListener::__construct(ConfigManager $configManager, RouterInterface $router, $logsDir)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/TrackingBundle/EventListener/ConfigListener.php#L28 "Oro\Bundle\TrackingBundle\EventListener\ConfigListener")</sup>
* The following properties in class `ConfigListener`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/TrackingBundle/EventListener/ConfigListener.php#L14 "Oro\Bundle\TrackingBundle\EventListener\ConfigListener")</sup> were removed:
   - `$dynamicTrackingRouteName`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/TrackingBundle/EventListener/ConfigListener.php#L14 "Oro\Bundle\TrackingBundle\EventListener\ConfigListener::$dynamicTrackingRouteName")</sup>
   - `$prefix`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/TrackingBundle/EventListener/ConfigListener.php#L19 "Oro\Bundle\TrackingBundle\EventListener\ConfigListener::$prefix")</sup>
   - `$keys`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/TrackingBundle/EventListener/ConfigListener.php#L24 "Oro\Bundle\TrackingBundle\EventListener\ConfigListener::$keys")</sup>

