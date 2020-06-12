# Oro\Bundle\CampaignBundle\Entity\Campaign

## ACTIONS

### get

Retrieve a specific campaign record.

{@inheritdoc}

### get_list

Retrieve a collection of campaign records.

{@inheritdoc}

### create

Create a new campaign record.

The created record is returned in response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "campaigns",
      "attributes": {
         "name": "Campaign Name",
         "code": "CampaignCode",
         "description": "Campaign description",
         "budget" : "100"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "users",
               "id": "44"
            }
         },
         "organization": {
            "data": {
               "type": "organizations",
               "id": "1"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific campaign record.

The updated record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

```JSON
{
   "data": {
      "type": "campaigns",
      "id": "1",
      "attributes": {
         "name": "Campaign Name",
         "code": "CampaignCode1",
         "description": "Campaign description",
         "budget" : "100"
      },
      "relationships": {
         "owner": {
            "data": {
               "type": "users",
               "id": "44"
            }
         },
         "organization": {
            "data": {
               "type": "organizations",
               "id": "1"
            }
         }
      }
   }
}
```
{@/request}

### delete

Delete a specific campaign record.

{@inheritdoc}

### delete_list

Delete a campaign record.

{@inheritdoc}

## FIELDS

### reportPeriod

#### get, create, update

{@inheritdoc}

The supported values are `day`, `hour`, `month`.

#### get_list, create_list, update_list

{@inheritdoc}

The supported values are `day`, `hour`, `month`.

### budget

Specify a budget for a marketing campaign. The minimal value is 0.

### description

Provide a description of a marketing campaign.

### startDate

Specify the date which the campaign starts from.

### endDate

Specify the date which the campaign finishes on.

## SUBRESOURCES

### Owner

#### get_subresource

Retrieve the record of the user who is the owner of a specific campaign record.

#### get_relationship

Retrieve the ID of the user who is the owner of a specific campaign record.

#### update_relationship

Replace the owner of a specific campaign record.

{@request:json_api}
Example:

```JSON
{
  "data": {
    "type": "users",
    "id": "37"
  }
}
```
{@/request}

### Organization

#### get_subresource

Retrieve the record of the organization that a specific campaign belongs to.

#### get_relationship

Retrieve the ID of the organization that a specific campaign record belongs to.

#### update_relationship

Replace the organization that a specific campaign record belongs to.

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
