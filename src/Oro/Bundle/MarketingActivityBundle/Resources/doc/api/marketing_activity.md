# Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity

## ACTIONS

### get

The **get** action retrieves a specific marketing activity record.

{@inheritdoc}

### get_list

The **get_list** action retrieves a collection of marketing activity records.

A list of the records to be returned is limited by filters.

{@inheritdoc}

### create

The action creates a new marketing activity record.

The created record is returned in response.

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

The **update** action edits a specific marketing activity record.

The updated record is returned in response.

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

The action deletes a specific marketing activity record.

{@inheritdoc}

### delete_list

The **delete_list** deletes a collection of marketing activity records.

A list of the records to be deleted is limited by filters.

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

The **get_subresource** action retrieves the record of a marketing activity type assigned to a specific marketing activity record.

#### get_relationship

The **get_relationship** action retrieves the ID of a marketing activity type record assigned to a specific marketing activity record.

#### update_relationship

The **update_relationship** action replaces the marketing activity type assigned to a specific marketing activity record.

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

The **get_subresource** action retrieves the record of the organization that is the owner of a specific marketing activity record.

#### get_relationship

The **get_relationship** action retrieves the ID of the organization that is the owner of a specific marketing activity record.

#### update_relationship

The **update_relationship** replaces the owner of a specific marketing activity record.

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

The **get** action retrieves a specific marketing activity type record, such as click, open, send, unsubscribe, soft_bunce, and hard_bounce. 

### get_list

The **get_list** retrieves a collection of marketing activity type records, such as click, open, send, unsubscribe, soft_bunce, and hard_bounce.

A list of the records to be returned is limited by filters.

