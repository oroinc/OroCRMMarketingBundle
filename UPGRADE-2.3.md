UPGRADE FROM 2.2 to 2.3
========================

MarketingListBundle
-------------------
- Tracking script `tracking.php` moved to `Oro/Bundles/TrackingBundle/Resources/public/lib/tracking.php`
- Class `Oro/Bundle/TrackingBundle/Migration/TrackingScriptInstaller` removed
    - tracking script installation (copying `tracking.php` into web folder) replaced with assets
    - see `Oro/Bundle/TrackingBundle/Resources/config/oro/app.yml`
- Class `Oro/Bundle/TrackingBundle/DependencyInjection/Configuration`
    - removed configuration option `oro_tracking.web_root`
