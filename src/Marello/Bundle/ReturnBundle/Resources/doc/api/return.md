# Marello\Bundle\ReturnBundle\Entity\ReturnEntity

## ACTIONS

### get

Retrieve a specific return record.

{@inheritdoc}

### get_list

Retrieve a collection of return records.

The list of records that will be returned, could be limited by <a href="https://doc.oroinc.com/api/filters">filters</a>.

{@inheritdoc}

### create

Create a new return record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}

`</web_backend_prefix/api/marelloreturns>`

```JSON
{
  "data": {
    "type": "marelloreturns",
    "attributes": {
      "returnReference": "2345678"
    },
    "relationships": {
      "order": {
        "data": {
          "type": "marelloorders",
          "id": "1"
        }
      },
      "returnItems": {
        "data": [
          {
            "type": "marelloreturnitems",
            "id": "8da4d8e7-6b25-4c5c-8075-nh3fpu9sca3htc3v"
          }
        ]
      },
      "salesChannel": {
        "data": {
          "type": "marellosaleschannels",
          "id": "chan_usd"
        }
      },
      "organization": {
        "data": {
          "type": "organizations",
          "id": "1"
        }
      }
    }
  },
  "included": [
    {
      "type": "marelloreturnitems",
      "id": "8da4d8e7-6b25-4c5c-8075-nh3fpu9sca3htc3v",
      "attributes": {
        "quantity": 1
      },
      "relationships": {
         "orderItem": {
          "data": {
            "type": "marelloorderitems",
            "id": "1"
          }
        },
        "reason": {
          "data": {
            "type": "marelloreturnreasons",
            "id": "damaged"
          }
        }
      }
    }
  ]
}
```
{@/request}

## FIELDS

### returnReference

#### create

{@inheritdoc}

### order

#### create

{@inheritdoc}

**Required field**

### returnItems

#### create

{@inheritdoc}

**Required field**

### salesChannel

#### create

{@inheritdoc}

**Required field**

### organization

#### create

{@inheritdoc}

**Required field**

### workflowStep

Current step in workflow

### workflowItem

Workflow Item related to entity
