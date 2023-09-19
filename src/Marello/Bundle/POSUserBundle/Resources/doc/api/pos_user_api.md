# Marello\Bundle\POSUserBundle\Api\Model\POSUserApi

## ACTIONS

### put

Retrieve a api key for the logged in User.

{@inheritdoc}

{@request:json_api}
Example:

`<web_backend_prefix/api/marelloposuser/authenticate>`

```JSON
{  
   "data":{  
      "type":"marelloposuser",
      "attributes":{
         "apiKey":"62674f15490a74cf0c607a83274d3883d8099496",
         "roles": [
           "ROLE_POS_ADMIN"
         ]
      }
   }
}
```
{@/request}

## FIELDS

### username
Username of the Instore User account.

#### put

User can be verified by either the username or email.

### email
Email of the Instore User account.

#### put

User can be verified by either the username or email.

### credentials

Password of the user for verfication of the account.

**The required field**

#### put

Password is used to verify the identity of the user.


#####NOTE: the specifications are based on proposed specs of a login session
#####Resources:
* https://accountjsonapi.docs.apiary.io/#reference/current-user/session/sign-in
* http://discuss.jsonapi.org/t/example-json-api-for-accounts/234/11