# Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity

## ACTIONS

### get

Retrieve a specific marketing activity record.

{@inheritdoc}

### get_list

Retrieve a collection of marketing activity records.

The list of records that will be returned, could be limited by filters.

{@inheritdoc}

### create

Create a new marketing activity record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

`< /admin/api/marketingactivities>`

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

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

`</admin/api/marketingactivities/1>`

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

Delete a specific marketing activity record.

{@inheritdoc}

### delete_list

Delete a collection of marketing activity records.

The list of records that will be deleted, could be limited by filters.

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

Retrieve the campaign record a specific marketing activity record is assigned to.

#### get_relationship

Retrieve the ID of the campaign record which a specific marketing activity record is assigned to.

#### update_relationship

Replace campaign a specific marketing activity record is assigned to.

{@request:json_api}
Example:

`</admin/api/marketingactivities/1/relationships/campaign>`

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

Retrieve a record of marketing activity type assigned to a specific marketing activity record.

#### get_relationship

Retrieve ID of marketing activity type record assigned to a specific marketing activity record.

#### update_relationship

Replace marketing activity type assigned to a specific marketing activity record.

{@request:json_api}
Example:

`</admin/api/marketingactivities/1/relationships/marketingActivityType>`

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

`</admin/api/marketingactivities/1/relationships/owner>`

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

Retrieve a specific marketing activity type record.

A type of marketing activities (click, open, send, unsubscribe, soft_bunce, hard_bounce). 

### get_list

Retrieve a collection of marketing activity type records.

The list of records that will be returned, could be limited by filters.

A type of marketing activities (click, open, send, unsubscribe, soft_bunce, hard_bounce). 
