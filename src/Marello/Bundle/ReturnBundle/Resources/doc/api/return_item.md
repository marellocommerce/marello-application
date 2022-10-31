# Marello\Bundle\ReturnBundle\Entity\ReturnItem

## ACTIONS

### get

Retrieve a specific return item record.

{@inheritdoc}

### get_list

Retrieve a collection of return item records.

The list of records that will be returned, could be limited by <a href="https://doc.oroinc.com/api/filters">filters</a>.

{@inheritdoc}

### create

Create a new return item record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}

`</web_backend_prefix/api/marelloreturnitems>`

```JSON
{
  "data": {
    "type": "marelloreturnitems",
    "attributes": {
      "quantity": 1
    },
    "relationships": {
      "orderitem": {
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
}
```
{@/request}

## FIELDS

### quantity

#### create
Quantity of the returned item, this cannot exceed the quantity ordered

### orderitem

#### create

{@inheritdoc}

**Required field**

### reason

#### create

{@inheritdoc}

**Required field**
