# OroMarketingListBundle

OroMarketingListBundle adds Marketing Lists entity to the Oro application to enable customer segmentation in the marketing campaigns.

The bundle provides UI for users to create and manage marketing lists using the capabilities of [OroSegmentBundle](https://github.com/oroinc/platform/tree/master/src/Oro/Bundle/SegmentBundle).

## Backend Implementation

### Entities

The **Marketing Lists** entity has a list of the filtered **Segment** entities with a virtual **Contact Information** field.

The example of how to add an entity to **Marketing Lists** is described below:

```yml
# Acme/Bundle/DemoBundle/Resources/config/oro/entity.yml

oro_entity:
    virtual_fields:
        Acme\Bundle\DemoBundle\Entity\Account:
            contactInformation:
                query:
                    select:
                        expr: defaultContact.email
                        return_type:  string
                    join:
                        left:
                            - { join: entity.defaultContact, alias: defaultContact }
```


**IMPORTANT:** Please, pay attention that while selecting **one-to-many** relation, the **contact information** should be defined without ambiguity, otherwise, the system is unclear which contact information to use.
