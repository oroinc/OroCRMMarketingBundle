- [CampaignBundle](#campaignbundle)
- [MarketingListBundle](#marketinglistbundle)

CampaignBundle
--------------
* The `CampaignCodeValidator`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/CampaignBundle/Validator/CampaignCodeValidator.php#L12 "Oro\Bundle\CampaignBundle\Validator\CampaignCodeValidator")</sup> class was removed.
* The `CampaignCode::validatedBy`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/CampaignBundle/Validator/Constraints/CampaignCode.php#L25 "Oro\Bundle\CampaignBundle\Validator\Constraints\CampaignCode::validatedBy")</sup> method was removed.
* The `EmailTransport::__construct(Processor $processor, EmailRenderer $emailRenderer, DoctrineHelper $doctrineHelper, EmailAddressHelper $emailAddressHelper)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/CampaignBundle/Transport/EmailTransport.php#L41 "Oro\Bundle\CampaignBundle\Transport\EmailTransport")</sup> method was changed to `EmailTransport::__construct(EmailModelSender $emailModelSender, EmailRenderer $emailRenderer, DoctrineHelper $doctrineHelper, EmailAddressHelper $emailAddressHelper)`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-rc/src/Oro/Bundle/CampaignBundle/Transport/EmailTransport.php#L34 "Oro\Bundle\CampaignBundle\Transport\EmailTransport")</sup>
* The `EmailTransport::$processor`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/CampaignBundle/Transport/EmailTransport.php#L24 "Oro\Bundle\CampaignBundle\Transport\EmailTransport::$processor")</sup> property was removed.

MarketingListBundle
-------------------
* The following classes were removed:
   - `ContactInformationColumnValidator`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/MarketingListBundle/Validator/ContactInformationColumnValidator.php#L13 "Oro\Bundle\MarketingListBundle\Validator\ContactInformationColumnValidator")</sup>
   - `ContactInformationColumnConstraint`<sup>[[?]](https://github.com/oroinc/OroCRMMarketingBundle/tree/5.0.0-beta.2/src/Oro/Bundle/MarketingListBundle/Validator/Constraints/ContactInformationColumnConstraint.php#L7 "Oro\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint")</sup>

