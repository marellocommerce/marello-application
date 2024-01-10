# Marello\Bundle\POSBundle\Api\Model\Login

## ACTIONS

### create

Validates the pos user email and password, and if the credentials are valid, returns the API access key if exist and the roles for the pos user.
This can be used for subsequent API requests.

{@request:json_api}
Example of the request:

```JSON
{
  "meta": {
    "user": "user@example.com",
    "password": "123"
  }
}
```

Example of the response:

```JSON
{
  "meta": {
    "apiKey": "2fae75ac8e15a82f499756fb905d2f80b0d0051e",
    "roles": [
      "ROLE_POS_USER",
      "ROLE_POS_ADMIN"
    ]
  }
}
```
{@/request}

## FIELDS

### user

The pos user email or username.

**The required field.**

### password

The pos user password.

**The required field.**

### apiKey

The API access key.

### roles

The roles for the pos user.