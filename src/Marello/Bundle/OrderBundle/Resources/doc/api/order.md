# Marello\Bundle\OrderBundle\Entity\Order

## ACTIONS

### get

Retrieve a specific order record.

{@inheritdoc}

### get_list

Retrieve a collection of order records.

The list of records that will be returned, could be limited by <a href="https://www.oroinc.com/doc/orocommerce/current/dev-guide/integration#filters">filters</a>.

{@inheritdoc}

### create

Create a new order record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}

Example without address:

`</web_backend_prefix/api/orders>`

```JSON
{
  "data": [
    {
      "type": "orders",
      "id": "1",
      "attributes": {
        "orderReference": null,
        "invoiceReference": null,
        "subtotal": "50.5000",
        "totalTax": "0.0000",
        "grandTotal": "60.5000",
        "currency": "EUR",
        "paymentMethod": null,
        "paymentReference": null,
        "paymentDetails": null,
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
        "data": [],
        "locale": null
      },
      "relationships": {
        "items": {
          "data": [
            {
              "type": "orderitems",
              "id": "1"
            }
          ]
        },
        "customer": {
          "data": {
            "type": "customers",
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
            "type": "saleschannels",
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

Example with address:

`</web_backend_prefix/api/orders>`

```JSON
{
   "data":{
      "type":"customers",
      "attributes":{
         "firstName":"Firstname",
         "lastName":"Lastname",
         "email":"new_customer@example.com"
      },
      "relationships":{
         "primaryAddress":{
            "data":[
               {
                  "type":"marelloaddresses",
                  "id":"1"
               }
            ]
         }
      }
   },
   "included":[
      {
         "type":"marelloaddresses",
         "id":"1",
         "attributes":{
            "firstName":"My Name",
            "lastName":"My Name",
            "email":"new_customer@example.com"
         },
         "relationships":{
            "country":{
               "data":[
                  {
                     "type":"countries",
                     "id":"US"
                  }
               ]
            },
            "region":{
               "data":[
                  {
                     "type":"regions",
                     "id":"US-NY"
                  }
               ]
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
