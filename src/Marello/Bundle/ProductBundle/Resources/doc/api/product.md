# Marello\Bundle\ProductBundle\Entity\Product

## ACTIONS  

### get

Retrieve a specific product record

{@inheritdoc}

### get_list

Retrieve a collection of products records.

{@inheritdoc}

### create

Create a new product record.

The created record is returned in the response.

{@request:json_api}
Example:

`<web_backend_prefix/api/products/{id}>`

```JSON
{
  "data": {
    "type": "products",
    "id": "100909",
    "attributes": {
      "productId": 848,
      "name": "9'4\" Super Magnum",
      "manufacturingCode": "TZH-529-udz-090",
      "productType": null,
      "cost": null,
      "weight": 12,
      "warranty": null,
      "data": []
    },
    "relationships": {
      "status": {
        "data": {
          "type": "productstatuses",
          "id": "enabled"
        }
      },
      "organization": {
        "data": {
          "type": "organizations",
          "id": "1"
        }
      },
      "prices": {
        "data": [
          {
            "type": "productprices",
            "id": "2542"
          }
        ]
      },
      "channelPrices": {
        "data": []
      },
      "channels": {
        "data": [
          {
            "type": "saleschannels",
            "id": "sales_channel_de_munchen"
          }
        ]
      },
      "taxCode": {
        "data": {
          "type": "marellotaxcodes",
          "id": "DE_high"
        }
      },
      "categories": {
        "data": [
          {
            "type": "categories",
            "id": "1"
          }
        ]
      },
      "image": {
        "data": {
          "type": "files",
          "id": "847"
        }
      }
    }
  }
}
```
{@/request}
