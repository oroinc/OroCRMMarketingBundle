OroMarketingListBundle
======================

The goal of `OroMarketingListBundle` is to provide entities segmentation used in marketing campaigns.

## Overview

`OroMarketingListBundle` improves the possibilities of `OroSegmentBundle`. Read [documentation](https://github.com/orocrm/platform/blob/master/src/Oro/Bundle/SegmentBundle/README.md) about `OroSegmentBundle`.

## Backend implementation

### Entities

**Marketing List** entities has list of filtered **Segment** entities with virtual field **Contact Information**.
Example how to add own entity to **Marketing List** entities list:

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


**IMPORTANT:** Please pay attention to **one-to-many** relations, the **contact information** should be defined without ambiguity (case with multiple related addresses, which one will be used).
