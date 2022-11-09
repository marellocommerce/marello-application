# Marello\Bundle\OrderBundle\Entity\Order

## ACTIONS

### get

Retrieve a specific order record.

{@inheritdoc}

### get_list

Retrieve a collection of order records.

The list of records that will be returned, could be limited by <a href="https://doc.oroinc.com/api/filters">filters</a>.

{@inheritdoc}

### create

Create a new order record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}

`</web_backend_prefix/api/marelloorders>`

```JSON
{
  "data": [
    {
      "type": "marelloorders",
      "id": "1",
      "attributes": {
        "orderReference": null,
        "invoiceReference": null,
        "subtotal": "50.5000",
        "totalTax": "0.0000",
        "grandTotal": "60.5000",
        "currency": "EUR",
        "paymentMethod": null,
        "shippingAmountInclTax": "10.0000",
        "shippingAmountExclTax": "10.0000",
        "shippingMethod": null,
        "shippingMethodType": null,
        "estimatedShippingCostAmount": null,
        "overriddenShippingCostAmount": "10.0000",
        "discountAmount": null,
        "discountPercent": null,
        "couponCode": null,
        "invoicedAt": null,
        "data": []
      },
      "relationships": {
        "items": {
          "data": [
            {
              "type": "marelloorderitems",
              "id": "1"
            }
          ]
        },
        "customer": {
          "data": {
            "type": "marellocustomers",
            "id": "4"
          }
        },
        "billingAddress": {
          "data": {
            "type": "marelloaddresses",
            "id": "2010"
          }
        },
        "shippingAddress": {
          "data": {
            "type": "marelloaddresses",
            "id": "2011"
          }
        },
        "salesChannel": {
          "data": {
            "type": "marellosaleschannels",
            "id": "6"
          }
        },
        "localization": {
          "data": null
        },
        "organization": {
          "data": {
            "type": "organizations",
            "id": "1"
          }
        }
      }
    }
  ]
}
```
{@/request}

### update

Update an existing customer record.

The updated record is returned in the response.

{@inheritdoc}

## FIELDS

### workflowStep

Current step in workflow

### workflowItem

Workflow Item related to entity

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*
