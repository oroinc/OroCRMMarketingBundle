# Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity

## ACTIONS

### get

Retrieve a specific marketing activity record.

{@inheritdoc}

### get_list

Retrieve a collection of marketing activity records.

{@inheritdoc}

### create

The action creates a new marketing activity record.

The created record is returned in response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "marketingactivities",
    "attributes": {
      "entityId": "2",
      "entityClass": "Oro\\Bundle\\EmailBundle\\Entity\\Email",
      "actionDate": "2017-08-21T17:27:14Z"
    },
    "relationships": {
      "owner": {
        "data": {
          "type": "organizations",
          "id": "1"
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
          "type": "matypes",
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
    "attributes": {
      "entityId": "2",
      "entityClass": "Oro\\Bundle\\EmailBundle\\Entity\\Email"
    },
    "relationships": {
      "owner": {
        "data": {
          "type": "organizations",
          "id": "1"
        }
      },
      "marketingActivityType": {
        "data": {
          "type": "matypes",
          "id": "click"
        }
      }
    }
  }
}
```
{@/request}

### delete

The action deletes a specific marketing activity record.

{@inheritdoc}

### delete_list

Delete a collection of marketing activity records.

{@inheritdoc}

## FIELDS

### actionDate

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### entityClass

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### entityId

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*

### marketingActivityType

#### create

{@inheritdoc}

**The required field**

## SUBRESOURCES

### campaign

#### get_subresource

**Get_subresource** retrieves the campaign record which a specific marketing activity record is assigned to.

#### get_relationship

**Get_relationship** retrieves the ID of the campaign record which a specific marketing activity record is assigned to.

#### update_relationship

**Update_relationship** replaces the campaign which a specific marketing activity record is assigned to.

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
    "type": "matypes",
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

# Extend\Entity\EV_Ma_Type

## ACTIONS

### get

Retrieve a specific marketing activity type record, such as click, open, send, unsubscribe, soft_bunce, and hard_bounce.

### get_list

Retrieve a collection of marketing activity type records, such as click, open, send, unsubscribe, soft_bunce, and hard_bounce.
