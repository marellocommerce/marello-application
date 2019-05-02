# Marello\Bundle\PricingBundle\Entity\ProductPrice

## ACTIONS

### get

Retrieve a specific productprice record.

{@inheritdoc}

### get_list

Retrieve a collection of productprice records.

The list of records that will be returned, could be limited by <a href="https://www.oroinc.com/doc/orocommerce/current/dev-guide/integration#filters">filters</a>.

{@inheritdoc}

### create

Create a new productprice record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}

`</web_backend_prefix/api/returns>`

```JSON
{
  "data": {
    "type": "returnitems",
    "attributes": {
      "quantity": 1
    },
    "relationships": {
      "orderitem": {
        "data": {
          "type": "orderitems",
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