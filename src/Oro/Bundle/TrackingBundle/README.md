# OroTrackingBundle

OroTrackingBundle enables management console administrators to set up and configure a tracking code that can be embedded into the Oro application or into a third-party website to enable customer behavior tracking.

The bundle allows to collect this data and provides UI for reporting the collected events and the customer behavior actions.

### Notes

If the Piwik synchronization is enabled, the value of the tracking website's "identifier" fields should be the same as the Piwik website's ID (integer value).

### TrackingProcessor

The main goal of the tracking events processing is to identify the objects which the events belong to. For example, it can be a customer identification form used by any integrated system, such as eCommerce, blog, project management application, etc.

#### How It Works

- Web events are collected by a tracking.php front controller, using another HTTP request to prod application with all the request data.

- TrackingDataController launches a new "import_request_to_database" job with all the data from the ($request->query->all()) query.

- `Oro\Bundle\TrackingBundle\ImportExport\DataConverter` transforms the data to match TrackingData.

- `Oro\Bundle\TrackingBundle\Entity\TrackingEvent` is created as a relation from `Oro\Bundle\TrackingBundle\Entity\TrackingData` by `Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer`.

- The writer saves the tracking data to db.

- Then, the cron starts the "oro:cron:tracking:parse" command and uses `Oro\Bundle\TrackingBundle\Processor\TrackingProcessor` to process the web events.

- `Oro\Bundle\TrackingBundle\Processor\TrackingProcessor` reads the data from the "oro_tracking_event" and "oro_tracking_data" tables. It also calls the `Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentificationProvider` chain provider that uses another providers to fill the "oro_tracking_visit" and "oro_tracking_visit_event" tables. 

- At the same time, it collects the web tracking event names and fills the dictionary represented in the "oro_tracking_event_dictionary" table. Initially, tracking data comes from the outside as JSON does, which may decelerate the identification recognitions, or future reports, segments, and charts building. So, to avoid any possible blocks, we are optimizing the data structure.

- This command can be executed manually via the command line. By default, it is executed every 15 mins via JobQueue (cronjob).

- The next stage is identification. It is represented with the "**TrackingEventIdentificationProvider**" chain provider and the "**oro_tracking.provider.identifier_provider**" service. You can implement own identification provider for your purposes. The only requirement is that it should implement "**TrackingEventIdentifierInterface**" and be registered in services with the "**oro_tracking.provider.identification**" tag. Also, you can prioritise your provider with a priority parameter.

- Please note, that the input data for such provider is the "**TrackingVisit**" object.

- To connect the tracking event with your data, the provider should have three additional methods: **isApplicableVisitEvent**, **processEvent**, **getEventTargets**.

### Request parameters expected by tracking

The actual mapping data is represented in `Oro\Bundle\TrackingBundle\ImportExport\DataConverter` and 
is filled by Piwik automatically, otherwise, a client should fill it in a custom code.

* e_n - event name
* e_v - event value
* action_name
* idsite - site id
* _uid - user identifier
* _rcn - code (e.g. Campaign code)
* _id - visit id, required, should represent a unique visit id


#### Example

You can find a full working code in OroCRM MarketingCRM Bridge -> Provider -> TrackingCustomerIdentification. 

A simple example is represented below:

##### Services

```yaml

    acme_test.provider.tracking_customer_identificator:
        class: Acme\Bundle\AcmeBundle\Provider\TestCustomerIdentification
        tags:
           - {name: oro_tracking.provider.identification, priority: 10}
```

##### Code

``` php

namespace Acme\Bundle\AcmeBundle\Provider;

use ...

class TestCustomerIdentification implements TrackingEventIdentifierInterface
{
    /**
     * {@inheritdoc}
     */
    public function isApplicable(TrackingVisit $trackingVisit)
    {
        /**
         * Here we checks if given tracking visit can be identified by our provider.
         */
        if (...) {
            return true;
        }

        return false;
    }

    /**
     * The main logic, in most cases it will be the same.
     *
     * {@inheritdoc}
     */
    public function identify(TrackingVisit $trackingVisit)
    {
        $userIdentifier = $trackingVisit->getParsedUID() > 0
            ? $trackingVisit->getParsedUID()
            : $this->parse($trackingVisit->getUserIdentifier());
        if ($userIdentifier) {
            $result = [
                'parsedUID'    => $userIdentifier,
                'targetObject' => null
            ];

            $target = $this->em->getRepository($this->getTarget())->findOneBy([ {columnName} => $userIdentifier ]);
            if ($target) {
                $result['targetObject'] = $target;
            }

            return $result;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityTarget()
    {
        /**
         * Here we should return object's class name for which given tracking visit will be assigned to.
         */
    }
    
    /**
     * {@inheritdoc}
     */
    public function isApplicableVisitEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        /**
         * should return true if this processor can process given visit event
         */
    }
    
    /**
     * {@inheritdoc}
     */
    public function processEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        /**
         *  Here should be some logic that returns array with target entity classes
         */
    }

    /**
     * {@inheritdoc}
     */
    public function getEventTargets()
    {
        /**
         *  Should return array with necessary event targets 
         */
    }

    /**
     * Parse user identifier string and returns value by which identity object can be retrieved.
     * Returns null in case identifier is not found.
     *
     * @param string $identifier
     *
     * @return string|null
     */
    protected function parse($identifier = null)
    {
        if (!empty($identifier)) {
            /**
             * Actually parser for user identifier string
             */
        }

        return null;
    }
}
```

### Tracked data in report builder

A user can create reports based on the tracked event data.

The main entity for this data is **Visitor event** which is parsed web event data related to a customer, campaign order or other customer data. This entity has the following fields:

 - **Type**. A virtual string field. A type of the event. Each tracking website can use its own list of event types.
 
 - **IP**. A virtual string field. The IP address of a visitor.
 
 - **URL**. A virtual string field the URL action comes from.
 
 - **Title**. A virtual string field the title of page action comes from.
  
 - **Bot**. A virtual boolean field which indicates whether a visitor is a bot.
  
 - **Client name**. A virtual string field. A visitor's client name (e.g., Firefox, Chrome).
  
 - **Client type**. A virtual string field. A visitor's client type (e.g., Browser).
  
 - **Client version**. A virtual string field. The version number of a visitor's client.
 
 - **OS**. A virtual string field. A visitor's operating system name. (e.g., Windows, Mac).
 
 - **OS version**. A virtual string field. A visitor's operating system name.(e.g., XP, 10.10).
 
 - **Desktop**. A virtual boolean field. Set to true if a visitor comes from a desktop system.
 
 - **Mobile**. A virtual boolean field. Set to true if a visitor comes from a mobile system. 
 
 - **Identified**. A virtual boolean field. Set to true if a visitor is detected. (Non-anonymous event).
 
 - **Event date**. A virtual datetime field. The date when event is executed.
 
 - **Tracking website**. A link to a website tracking configuration record.
 
 - List of connected records to the event entity.

Additionally, there is a **Tracking Event** table - the original web event data recorded from the website.


### Configuration

In order to collect the information, you need to copy the `Resources/lib/tracking.php` file to the application's `/web` folder. This is performed automatically, but if your application has different `/web` folder, you need to configure it in `config/config.yml`. For example:

```yaml
oro_tracking:
    web_root: %kernel.project_dir%/public
```

### Security firewalls

TrackingBundle comes with a default firewall configuration to enable the data to be pushed into the system:

```yaml
security:
    firewalls:
        tracking_data:
            pattern:   ^/tracking/data/create
            provider:  chain_provider
            anonymous: true
```

However, it can be overwritten to fit your needs, in 2 different ways:

#### 1. Application's ``security.yml``

```yaml
security:
    firewalls:
        # override pattern
        tracking_data:
            pattern:   ^%web_backend_prefix%/tracking/data/create
```

#### 2. Bundle's ``app.yml`` in ``Resources/config/oro``

```yaml
security:
    firewalls:
        # override pattern
        tracking_data:
            pattern:   ^%web_backend_prefix%/tracking/data/create
```
