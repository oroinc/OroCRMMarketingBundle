UPGRADE FROM 2.2 to 2.3
========================

CampaignBundle
--------------
- Class `Oro\Bundle\CampaignBundle\Form\EventListener\TransportSettingsEmailTemplateListener`
    - changed the constructor signature: parameter `SecurityContextInterface $securityContext` was replaced with `TokenAccessorInterface $tokenAccessor`
    - property `securityContext` was replaced with `tokenAccessor`

MarketingListBundle
-------------------
- Tracking script `tracking.php` moved to `Oro/Bundles/TrackingBundle/Resources/public/lib/tracking.php`
- Class `Oro/Bundle/TrackingBundle/Migration/TrackingScriptInstaller` removed
    - tracking script installation (copying `tracking.php` into web folder) replaced with assets
    - see `Oro/Bundle/TrackingBundle/Resources/config/oro/app.yml`
- Class `Oro/Bundle/TrackingBundle/DependencyInjection/Configuration`
    - removed configuration option `oro_tracking.web_root`
* The `Oro\Bundle\MarketingListBundle\Provider\ContactInformationExclusionProvider::__construct(Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider $entityConfigProvider, Doctrine\Common\Persistence\ManagerRegistry $managerRegistry)` method was changed to `Oro\Bundle\MarketingListBundle\Provider\ContactInformationExclusionProvider::__construct(Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface $virtualFieldProvider)`
* The following properties in class `Oro\Bundle\MarketingListBundle\Provider\ContactInformationExclusionProvider` were removed:
   - `$entityConfigProvider`
   - `$managerRegistry`
