# MarelloEnterprise\Bundle\InstoreAssistant\Api\Model\InstoreUserApi

## ACTIONS  

### get

Retrieve a api key for the logged in Instore User.

{@inheritdoc}

{@request:json_api}
Example:

`</api/instore-user>`

```JSON
{  
   "data":{  
      "type":"instoreusers",
      "attributes":{  
         "apiKey":"62674f15490a74cf0c607a83274d3883d8099496"
      }
   }
}
```
{@/request}

## FIELDS

### username
Username of the Instore User account.

#### get

User can be verified by either the username or email.

### email
Email of the Instore User account.

#### get

User can be verified by either the username or email.

### credentials

Password of the user for verfication of the account.

**The required field**

#### get

Password is used to verify the identity of the user.