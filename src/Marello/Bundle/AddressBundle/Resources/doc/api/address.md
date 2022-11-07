# Marello\Bundle\AddressBundle\Entity\MarelloAddress

## ACTIONS

### get

Retrieve a specific address record.

{@inheritdoc}

### get_list

Retrieve a collection of address records.

The list of records that will be returned, could be limited by <a href="https://doc.oroinc.com/api/filters">filters</a>.

{@inheritdoc}

### create

Create a new address record.

The created record is returned in the response.

{@inheritdoc}

{@request:json_api}

Example without address:

`</web_backend_prefix/api/marelloaddresses>`

```JSON
{
   "data":{
      "type":"marellocustomers",
      "attributes":{
         "firstName":"Firstname",
         "lastName":"Lastname",
         "email":"new_customer@example.com"
      }
   }
}
```
{@/request}

### update

Update an existing address record.

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