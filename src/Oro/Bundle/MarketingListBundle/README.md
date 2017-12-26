# OroMarketingListBundle

The goal of `OroMarketingListBundle` is to provide the entities segmentation used in marketing campaigns.

## Overview

`OroMarketingListBundle` improves the possibilities of `OroSegmentBundle`. Read [documentation](https://github.com/orocrm/platform/blob/master/src/Oro/Bundle/SegmentBundle/README.md) about `OroSegmentBundle`.

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
