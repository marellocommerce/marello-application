# Marello\Bundle\OrderBundle\Entity\Customer

## ACTIONS

### get

Retrieve a specific customer record.

{@inheritdoc}

### get_list

Retrieve a collection of customer records.

The list of records that will be returned, could be limited by <a href="https://www.oroinc.com/doc/orocommerce/current/dev-guide/integration#filters">filters</a>.

{@inheritdoc}

### create

Create a new customer record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}

Example without address:

`</web_backend_prefix/api/customers>`

```JSON
{
   "data":{
      "type":"customers",
      "attributes":{
         "firstName":"Firstname",
         "lastName":"Lastname",
         "email":"new_customer@example.com"
      }
   }
}
```

Example with address:

`</web_backend_prefix/api/customers>`

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

### firstName
### lastName
### email

#### create

{@inheritdoc}

**The required field**

#### update

{@inheritdoc}

**Please note:**

*This field is **required** and must remain defined.*