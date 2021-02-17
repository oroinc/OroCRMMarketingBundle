# Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity

## ACTIONS

### get

Retrieve a specific marketing activity record.

{@inheritdoc}

### get_list

Retrieve a collection of marketing activity records.

{@inheritdoc}

### create

Create a new marketing activity record.

The created record is returned in response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "marketingactivities",
    "attributes": {
      "actionDate": "2017-08-21T17:27:14Z"
    },
    "relationships": {
      "owner": {
        "data": {
          "type": "organizations",
          "id": "1"
        }
      },
      "entity": {
        "data": {
          "type": "emails",
          "id": "2"
        }
      },
      "campaign": {
        "data": {
          "type": "campaigns",
          "id": "1"
        }
      },
      "marketingActivityType": {
        "data": {
          "type": "marketingactivitytypes",
          "id": "click"
        }
      }
    }
  }
}
```
{@/request}

### update

Edit a specific marketing activity record.

The updated record is returned in response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "marketingactivities",
    "id": "1", 
    "relationships": {
      "entity": {
        "data": {
          "type": "emails",
          "id": "2"
        }
      },
      "marketingActivityType": {
        "data": {
          "type": "marketingactivitytypes",
          "id": "click"
        }
      }
    }
  }
}
```
{@/request}

### delete

Delete a specific marketing activity record.

{@inheritdoc}

### delete_list

Delete a collection of marketing activity records.

{@inheritdoc}

## FIELDS

### actionDate

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### entity

The entity to which this marketing activity record is related.

#### create

{@inheritdoc}

**The required field.**

#### update

{@inheritdoc}

**This field must not be empty, if it is passed.**

### marketingActivityType

#### create

{@inheritdoc}

**The required field.**

### relatedCampaign

The marketing campaign that the marketing activity took place within.

## SUBRESOURCES

### campaign

#### get_subresource

Retrieve the campaign record which a specific marketing activity record is assigned to.

#### get_relationship

Retrieve the ID of the campaign record which a specific marketing activity record is assigned to.

#### update_relationship

Replace the campaign which a specific marketing activity record is assigned to.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "campaigns",
    "id": "1"
  }
}
```
{@/request}

### entity

#### get_subresource

Retrieve the entity to which this marketing activity record is related.

#### get_relationship

Retrieve the ID of the entity to which this marketing activity record is related.

#### update_relationship

Retrieve the entity to which this marketing activity record is related.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "emails",
    "id": "2"
  }
}
```
{@/request}

### marketingActivityType

#### get_subresource

Retrieve the record of a marketing activity type assigned to a specific marketing activity record.

#### get_relationship

Retrieve the ID of a marketing activity type record assigned to a specific marketing activity record.

#### update_relationship

Replace the marketing activity type assigned to a specific marketing activity record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "marketingactivitytypes",
    "id": "open"
  }
}
```
{@/request}

### owner

#### get_subresource

Retrieve the record of the organization that is the owner of a specific marketing activity record.

#### get_relationship

Retrieve the ID of the organization that is the owner of a specific marketing activity record.

#### update_relationship

Replace the owner of a specific marketing activity record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "organizations",
    "id": "1"
  }
}
```
{@/request}

### relatedCampaign

#### get_subresource

Retrieve the marketing campaign that the marketing activity took place within.

#### get_relationship

Retrieve the ID of the marketing campaign that the marketing activity took place within.

#### update_relationship

Retrieve the marketing campaign that the marketing activity took place within.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "emailcampaigns",
    "id": "1"
  }
}
```
{@/request}

# Extend\Entity\EV_Ma_Type

## ACTIONS

### get

Retrieve a specific marketing activity type record.

### get_list

Retrieve a collection of marketing activity type records.
