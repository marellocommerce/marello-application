# Marello\Bundle\TaxBundle\Entity\TaxCode

## ACTIONS  

### get

Retrieve a tax code based of the TaxCode's code

{@inheritdoc}

{@request:json_api}
Example:

`<web_backend_prefix/api/marellotaxcodes/{id}>`

```JSON
{
  "data": {
    "type": "marellotaxcodes",
    "id": "DE_high"
  }
}
```
{@/request}


### get_list

Retrieve a list of tax codes

{@inheritdoc}

{@request:json_api}
Example:

`<web_backend_prefix/api/marellotaxcodes>`

```JSON
{
  "data": [
    {
      "type": "marellotaxcodes",
      "id": "DE_high"
    },
    {
      "type": "marellotaxcodes",
      "id": "FR_high"
    },
    {
      "type": "marellotaxcodes",
      "id": "UK_high"
    },
    {
      "type": "marellotaxcodes",
      "id": "US"
    }
  ]
}
```
{@/request}
