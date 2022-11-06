# Marello\Bundle\ProductBundle\Entity\Product

## ACTIONS  

### get

Retrieve a specific product record

{@inheritdoc}

### get_list

Retrieve a collection of products records.
The list of records that will be returned, could be limited by <a href="https://doc.oroinc.com/api/filters">filters</a>.

{@inheritdoc}

### create

Create a new product record.

The created record is returned in the response.

{@request:json_api}
Example:

`<web_backend_prefix/api/marelloproducts/{id}>`

```JSON
{
  "data": {
    "type": "marelloproducts",
    "id": "100909",
    "attributes": {
      "productId": 848,
      "denormalizedDefaultName": "9'4\" Super Magnum",
      "manufacturingCode": "TZH-529-udz-090",
      "productType": null,
      "weight": 12,
      "warranty": null,
      "data": []
    },
    "relationships": {
      "names": {
        "data": {
          "type": "localizedfallbackvalues",
          "id": 1
        }
      },
      "status": {
        "data": {
          "type": "marelloproductstatuses",
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
            "type": "marelloassembledpricelists",
            "id": "2542"
          },
          {
            "type": "marelloassembledpricelists",
            "id": "2543"
          },
          {
            "type": "marelloassembledpricelists",
            "id": "2544"
          }
        ]
      },
      "channelPrices": {
        "data": []
      },
      "channels": {
        "data": [
          {
            "type": "marellosaleschannels",
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
            "type": "marellocategories",
            "id": "1"
          }
        ]
      },
      "attributeFamily": {
        "data": {
          "type": "attributefamilies",
          "id": "1"
        }
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
