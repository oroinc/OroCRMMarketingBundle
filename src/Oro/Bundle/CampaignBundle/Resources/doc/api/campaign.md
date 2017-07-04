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
The created record is returned in the response.

{@inheritdoc}

{@request:json_api}
Example:

`</api/campaigns>`

```JSON
{
   "data":{
      "type":"campaigns",
      "attributes":{
         "name":"Campaign Name",
         "code":"CampaignCode",
         "description": "Campaign description",
         "budget" : "100"
      },
      "relationships":{
         "owner":{
            "data":{
               "type":"users",
               "id":"44"
            }
         },
         "organization":{
            "data":{
               "type":"organizations",
               "id":"1"
            }
         }
      }
   }
}
```
{@/request}

### update

Edit a specific campaign record.

{@inheritdoc}

{@request:json_api}
Example:

`</api/campaigns/1>`

```JSON
{
   "data":{
      "type":"campaigns",
      "id":"1",
      "attributes":{
         "name":"Campaign Name",
         "code":"CampaignCode1",
         "description": "Campaign description",
         "budget" : "100"
      },
      "relationships":{
         "owner":{
            "data":{
               "type":"users",
               "id":"44"
            }
         },
         "organization":{
            "data":{
               "type":"organizations",
               "id":"1"
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
The list of records to be deleted can be limited by filters.

{@inheritdoc}

## FIELDS

### reportPeriod

#### get, create, update
{@inheritdoc}
It supports the next values: `day`, `hour`, `month`.

#### get_list, create_list, update_list
{@inheritdoc}
It supports the next values: `day`, `hour`, `month`.

### budget
Budget of Marketing Campaign. Minimal value is 0.

### description
Description of Marketing Campaign.

### startDate
Campaign starts from this date.

### endDate
Campaign finishes on this date.

## SUBRESOURCES

### owner

#### get_subresource

Retrieve the record of the user who is the owner of a specific campaign record.

#### get_relationship

Retrieve the ID of the user who is the owner of a specific campaign record.

#### update_relationship


{@request:json_api}
Example:

`</api/campaigns/{id}/relationships/owner>`

```JSON
{
  "data": {
    "type": "users",
    "id": "37"
  }
}
```
{@/request}

### organization

#### get_subresource

Retrieve the record of the organization that a specific campaign belongs to.

#### get_relationship

Retrieve the ID of the organization that a specific campaign record belongs to.

#### update_relationship

Replace the organization that a specific campaign record belongs to.

{@request:json_api}
Example:

`</api/campaigns/{id}/relationships/organization>`

```JSON
{
  "data": {
    "type": "organizations",
    "id": "1"
  }
}
```
{@/request}
