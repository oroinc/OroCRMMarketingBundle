- [CampaignBundle](#campaignbundle)
- [TrackingBundle](#trackingbundle)

CampaignBundle
--------------
* The `Campaign::setStartDate($startDate)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-alpha.2/src/Oro/Bundle/CampaignBundle/Entity/Campaign.php#L251 "Oro\Bundle\CampaignBundle\Entity\Campaign")</sup> method was changed to `Campaign::setStartDate(DateTime $startDate = null)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/CampaignBundle/Entity/Campaign.php#L248 "Oro\Bundle\CampaignBundle\Entity\Campaign")</sup>
* The `EmailCampaign::setScheduledFor($scheduledFor)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-alpha.2/src/Oro/Bundle/CampaignBundle/Entity/EmailCampaign.php#L468 "Oro\Bundle\CampaignBundle\Entity\EmailCampaign")</sup> method was changed to `EmailCampaign::setScheduledFor(DateTime $scheduledFor = null)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/CampaignBundle/Entity/EmailCampaign.php#L465 "Oro\Bundle\CampaignBundle\Entity\EmailCampaign")</sup>

TrackingBundle
--------------
* The `ImportLogsCommand::__construct(DoctrineJobRepository $akeneoJobRepository, FeatureChecker $featureChecker, JobExecutor $jobExecutor, ConfigManager $configManager, $kernelLogsDir)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-alpha.2/src/Oro/Bundle/TrackingBundle/Command/ImportLogsCommand.php#L39 "Oro\Bundle\TrackingBundle\Command\ImportLogsCommand")</sup> method was changed to `ImportLogsCommand::__construct(DoctrineJobRepository $doctrineJobRepository, FeatureChecker $featureChecker, JobExecutor $jobExecutor, ConfigManager $configManager, $kernelLogsDir)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.1/src/Oro/Bundle/TrackingBundle/Command/ImportLogsCommand.php#L40 "Oro\Bundle\TrackingBundle\Command\ImportLogsCommand")</sup>

