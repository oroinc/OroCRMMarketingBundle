- [CampaignBundle](#campaignbundle)
- [MarketingListBundle](#marketinglistbundle)
- [TrackingBundle](#trackingbundle)

CampaignBundle
--------------
* The `Topics`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.1.0-beta.1/src/Oro/Bundle/CampaignBundle/Async/Topics.php#L8 "Oro\Bundle\CampaignBundle\Async\Topics")</sup> class was removed.
* The `TransportSettingsEmailTemplateListener::fillEmailTemplateChoices(FormInterface $form, $entityName)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.1.0-beta.1/src/Oro/Bundle/CampaignBundle/Form/EventListener/TransportSettingsEmailTemplateListener.php#L91 "Oro\Bundle\CampaignBundle\Form\EventListener\TransportSettingsEmailTemplateListener")</sup> method was changed to `TransportSettingsEmailTemplateListener::fillEmailTemplateChoices(FormInterface $form, $entityName)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.1.0-beta.2/src/Oro/Bundle/CampaignBundle/Form/EventListener/TransportSettingsEmailTemplateListener.php#L87 "Oro\Bundle\CampaignBundle\Form\EventListener\TransportSettingsEmailTemplateListener")</sup>
* The `EmailTemplateController::cgetAction($id = null)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.1.0-beta.1/src/Oro/Bundle/CampaignBundle/Controller/Api/Rest/EmailTemplateController.php#L28 "Oro\Bundle\CampaignBundle\Controller\Api\Rest\EmailTemplateController")</sup> method was changed to `EmailTemplateController::cgetAction($id = null, $includeNonEntity = true, $includeSystemTemplates = false)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.1.0-beta.2/src/Oro/Bundle/CampaignBundle/Controller/Api/Rest/EmailTemplateController.php#L24 "Oro\Bundle\CampaignBundle\Controller\Api\Rest\EmailTemplateController")</sup>

MarketingListBundle
-------------------
* The `UpdateMarketingListProcessor::__construct(DoctrineHelper $doctrineHelper, EventDispatcherInterface $dispatcher, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.1.0-beta.1/src/Oro/Bundle/MarketingListBundle/Async/UpdateMarketingListProcessor.php#L36 "Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor")</sup> method was changed to `UpdateMarketingListProcessor::__construct(DoctrineHelper $doctrineHelper, EventDispatcherInterface $dispatcher)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.1.0-beta.2/src/Oro/Bundle/MarketingListBundle/Async/UpdateMarketingListProcessor.php#L31 "Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor")</sup>

TrackingBundle
--------------
* The `Topics`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.1.0-beta.1/src/Oro/Bundle/TrackingBundle/Async/Topics.php#L5 "Oro\Bundle\TrackingBundle\Async\Topics")</sup> class was removed.
* The `AggregateTrackingVisitsProcessor::__construct(UniqueTrackingVisitDumper $trackingVisitDumper, ConfigManager $configManager, LoggerInterface $logger)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.1.0-beta.1/src/Oro/Bundle/TrackingBundle/Async/AggregateTrackingVisitsProcessor.php#L30 "Oro\Bundle\TrackingBundle\Async\AggregateTrackingVisitsProcessor")</sup> method was changed to `AggregateTrackingVisitsProcessor::__construct(UniqueTrackingVisitDumper $trackingVisitDumper, ConfigManager $configManager)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.1.0-beta.2/src/Oro/Bundle/TrackingBundle/Async/AggregateTrackingVisitsProcessor.php#L30 "Oro\Bundle\TrackingBundle\Async\AggregateTrackingVisitsProcessor")</sup>

