CampaignBundle
--------------
* The following classes were removed:
   - `CampaignController`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/CampaignBundle/Controller/Api/Rest/CampaignController.php#L20 "Oro\Bundle\CampaignBundle\Controller\Api\Rest\CampaignController")</sup>
   - `EmailCampaignController`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/CampaignBundle/Controller/Api/Rest/EmailCampaignController.php#L20 "Oro\Bundle\CampaignBundle\Controller\Api\Rest\EmailCampaignController")</sup>
* The following methods in class `Campaign`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/CampaignBundle/Entity/Campaign.php#L311 "Oro\Bundle\CampaignBundle\Entity\Campaign")</sup> were removed:
   - `getCreatedAt`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/CampaignBundle/Entity/Campaign.php#L311 "Oro\Bundle\CampaignBundle\Entity\Campaign::getCreatedAt")</sup>
   - `getUpdatedAt`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/CampaignBundle/Entity/Campaign.php#L321 "Oro\Bundle\CampaignBundle\Entity\Campaign::getUpdatedAt")</sup>
* The `CampaignRepository::getCampaignsByCloseRevenue`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/CampaignBundle/Entity/Repository/CampaignRepository.php#L112 "Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository::getCampaignsByCloseRevenue")</sup> method was removed.
* The `Campaign::setCombinedName($name, $code)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/CampaignBundle/Entity/Campaign.php#L355 "Oro\Bundle\CampaignBundle\Entity\Campaign")</sup> method was changed to `Campaign::setCombinedName($combinedName)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.1.0/src/Oro/Bundle/CampaignBundle/Entity/Campaign.php#L356 "Oro\Bundle\CampaignBundle\Entity\Campaign")</sup>
* The following properties in class `Campaign`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/CampaignBundle/Entity/Campaign.php#L152 "Oro\Bundle\CampaignBundle\Entity\Campaign")</sup> were removed:
   - `$createdAt::$createdAt`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/CampaignBundle/Entity/Campaign.php#L152 "Oro\Bundle\CampaignBundle\Entity\Campaign::$createdAt")</sup>
   - `$updatedAt::$updatedAt`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/CampaignBundle/Entity/Campaign.php#L166 "Oro\Bundle\CampaignBundle\Entity\Campaign::$updatedAt")</sup>

MarketingListBundle
-------------------
* The `ContactInformationFieldsExtension::__construct(ContactInformationFieldHelper $helper)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/MarketingListBundle/Twig/ContactInformationFieldsExtension.php#L19 "Oro\Bundle\MarketingListBundle\Twig\ContactInformationFieldsExtension")</sup> method was changed to `ContactInformationFieldsExtension::__construct(ContainerInterface $container)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.1.0/src/Oro/Bundle/MarketingListBundle/Twig/ContactInformationFieldsExtension.php#L19 "Oro\Bundle\MarketingListBundle\Twig\ContactInformationFieldsExtension")</sup>
* The `ContactInformationFieldsExtension::$helper`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/2.0.0/src/Oro/Bundle/MarketingListBundle/Twig/ContactInformationFieldsExtension.php#L14 "Oro\Bundle\MarketingListBundle\Twig\ContactInformationFieldsExtension::$helper")</sup> property was removed.

